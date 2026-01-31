<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/ticket.php");
require_once("../global/s3-client-wrapper/s3-client-wrapper.php");

if($_SESSION['ADMIN_PK_USER'] == 0 || $_SESSION['ADMIN_PK_USER'] == '' || $_SESSION['ADMIN_PK_ROLES'] != 1 ){ 
	header("location:../index");
	exit;
}
require_once('../school/send_notification.php');

if(!empty($_POST)){
	//echo "<pre>";print_r($_POST);exit;
	
	$TICKET = $_POST;
	unset($TICKET['ATTACHMENT']);
	if($_GET['id'] == '' ){
		
		$res = $db->Execute("SELECT MAX(TICKET_NO) AS TICKET_NO from Z_TICKET ");
		if($res->fields['TICKET_NO'] == 0)
			$TICKET_NO = 201;
		else
			$TICKET_NO = $res->fields['TICKET_NO'] + 1 ;
		
		if($TICKET['DUE_DATE'] != '')
			$TICKET['DUE_DATE'] = date("Y-m-d",strtotime($TICKET['DUE_DATE']));
			
		$TICKET['PK_TICKET_CATEGORY']  	= $_POST['PK_TICKET_CATEGORY'];
		$TICKET['PK_TICKET_STATUS']  	= $_POST['PK_TICKET_STATUS'];
		$TICKET['TICKET_FOR']  			= $_POST['TICKET_FOR'];
		$TICKET['IS_PARENT']  			= 1;
		$TICKET['TICKET_NO']  			= $TICKET_NO;
		$TICKET['PK_ACCOUNT']  			= -1;
		$TICKET['CREATED_BY']  			= $_SESSION['ADMIN_PK_USER'];
		$TICKET['CREATED_ON']  			= date("Y-m-d H:i");
		db_perform('Z_TICKET', $TICKET, 'insert');
		$PK_TICKET = $db->insert_ID();
		//echo "<pre>";print_r($TICKET);exit;
		$TICKET1['INTERNAL_ID'] 	= $PK_TICKET;
		$INTERNAL_ID				= $PK_TICKET;
		db_perform('Z_TICKET', $TICKET1, 'update'," PK_TICKET = '$PK_TICKET' ");
	} else {
		$INTERNAL_ID = $_GET['id'];
		
		$res = $db->Execute("SELECT * from Z_TICKET WHERE INTERNAL_ID = '$INTERNAL_ID' ");
		$TICKET['SUBJECT'] 				= $res->fields['SUBJECT'];
		$TICKET['TICKET_NO'] 			= $res->fields['TICKET_NO'];
		$TICKET['INTERNAL_ID'] 			= $res->fields['INTERNAL_ID'];
		$TICKET['PK_TICKET_PRIORITY'] 	= $res->fields['PK_TICKET_PRIORITY'];
		$TICKET['CLOSED_DATE'] 			= $res->fields['CLOSED_DATE'];
		$TICKET['PK_TICKET_STATUS']		= $res->fields['PK_TICKET_STATUS'];
		
		$TICKET['PK_ACCOUNT']  		= -1;
		$TICKET['CREATED_BY']  		= $_SESSION['ADMIN_PK_USER'];
		$TICKET['CREATED_ON']  		= date("Y-m-d H:i");
		//echo "<pre>";print_r($TICKET);exit;
		db_perform('Z_TICKET', $TICKET, 'insert');
		$PK_TICKET = $db->insert_ID();
	}
	
	$SEND_NOTIFICATION_DATA['PK_TICKET'] = $PK_TICKET;
	if($_GET['id'] == '') {
		send_notification($SEND_NOTIFICATION_DATA,'TICKET CREATED');
		
		$TICKET_STATUS_CHANGE['PK_TICKET'] 			= $PK_TICKET;
		$TICKET_STATUS_CHANGE['INTERNAL_ID'] 		= $PK_TICKET;
		$TICKET_STATUS_CHANGE['PK_TICKET_STATUS'] 	= 1;
		$TICKET_STATUS_CHANGE['CHANGED_BY'] 		= $_SESSION['ADMIN_PK_USER'];
		$TICKET_STATUS_CHANGE['CHANGED_ON']  		= date("Y-m-d H:i");
		db_perform('Z_TICKET_STATUS_CHANGE_HISTORY', $TICKET_STATUS_CHANGE, 'insert');
	} else {
		send_notification($SEND_NOTIFICATION_DATA,'COMMENTED ON TICKET');
	}
		
	//echo "<pre>";print_r($_FILES);exit;
	// $file_dir_1 = "../backend_assets/ticket_files/";
	$file_dir_1 = '../backend_assets/tmp_upload/';
	for($k = 0 ; $k < count($_FILES['ATTACHMENT']['name']) ; $k++){
		$name     = $_FILES['ATTACHMENT']['name'][$k];
		$name	  = str_replace("#","_",$name);
		$name	  = str_replace("&","_",$name);
		$tmp_name = $_FILES['ATTACHMENT']['tmp_name'][$k];
		$tmp_name = $_FILES['ATTACHMENT']['tmp_name'][$k];
		if (trim($name)!=""){				
			$extn   = explode(".",$name);
			$iindex	= count($extn) - 1;
			$rand_string = time().rand(10000,99999);
			$name1 = str_replace(".".$extn[$iindex],"",$name);
			$file11 = $name1.'-'.$_SESSION['ADMIN_PK_USER'].$rand_string.".".$extn[$iindex];						
			$newfile1 = $file_dir_1.$file11;	
			if(strtolower($extn[$iindex]) != 'php' && strtolower($extn[$iindex]) != 'html' && strtolower($extn[$iindex]) != 'js'){
				move_uploaded_file($tmp_name, $newfile1);					
				
				// Upload file to S3 bucket
				$key_file_name = 'backend_assets/ticket_files/'.$file11;
				$s3ClientWrapper = new s3ClientWrapper();
				$url = $s3ClientWrapper->uploadFile($key_file_name, $newfile1);

				$TICKET_ATTACHMENT = array(					
					'PK_TICKET'  	=> $PK_TICKET,
					// 'LOCATION' 		=> $newfile1,
					'LOCATION' 		=> $url,
					'FILE_NAME' 	=> $name,
					'UPLOADED_ON' 	=> date("Y-m-d H:i")
				);
				db_perform('Z_TICKET_ATTACHMENT', $TICKET_ATTACHMENT, 'insert');
				
				// delete tmp file
				unlink($newfile1);
			}
		}
	}
	//exit;
	header("location:manage_ticket");
}
if($_GET['id'] == ''){
	$TICKET_TYPE  	= '';
	$ACTIVE       	= '';
	
	$res = $db->Execute("SELECT MAX(TICKET_NO) AS TICKET_NO from Z_TICKET ");
	if($res->fields['TICKET_NO'] == 0)
		$TICKET_NO = 201;
	else
		$TICKET_NO = $res->fields['TICKET_NO'] + 1 ;
} else {
	$result = $db->Execute("select * from Z_TICKET WHERE INTERNAL_ID = '$_GET[id]' ");
	if($result->RecordCount() == 0){
		header("location:manage_ticket");
		exit;
	}
		
	$PK_TICKET_STATUS  	= $result->fields['PK_TICKET_STATUS'];
	$SUBJECT			= $result->fields['SUBJECT'];
	$TICKET_NO			= $result->fields['TICKET_NO'];
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
	<title><?=TICKET_PAGE_TITLE?> | <?=$title?></title>
	<style>
		h2 span {
			background: #fff;
			padding: 0 10px;
		}
		h2 {
			text-align: center;
			line-height: 0.1em;
			font-size: 15px;
		}
		h2 {
			width: 100%;
			text-align: center;
			border-bottom: 1px solid #000;
			line-height: 0.1em;
			margin: 10px 0 20px;
			font-size: 15px;
			padding-top: 10px;
		}
	</style>
</head>

<body class="horizontal-nav boxed skin-megna fixed-layout">
   <? require_once("pre_load.php"); ?>
    <div id="main-wrapper">
       <? require_once("menu.php"); ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                 <div class="row page-titles">
                    <div class="col-md-12 align-self-center">
                        <h4 class="text-themecolor"><? if($_GET['id'] == '') echo CREATE; else echo REPLY_TO; ?> <?=TICKET?> 
						<? if($_GET['id'] != '') echo ' # '.$TICKET_NO.' - '.$SUBJECT; ?>
						</h4>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <form class="floating-labels m-t-40" method="post" name="form1" id="form1" enctype="multipart/form-data" >
									<? if($_GET['id'] == ''){ ?>
									<div class="row">
										<div class="col-md-3">
											<div class="form-group m-b-40">
												<select name="PK_TICKET_CATEGORY" id="PK_TICKET_CATEGORY" class="form-control required-entry" >
													<option value=""></option>
													<? $res_dep = $db->Execute("select PK_TICKET_CATEGORY,TICKET_CATEGORY from Z_TICKET_CATEGORY WHERE ACTIVE = '1' ORDER BY TICKET_CATEGORY ASC ");
													while (!$res_dep->EOF) {  ?>
														<option class="<?=$class?>" value="<?=$res_dep->fields['PK_TICKET_CATEGORY']?>" ><?=$res_dep->fields['TICKET_CATEGORY']?></option>
													<?	$res_dep->MoveNext();
													} 	?>
												</select>
												<span class="bar"></span>
												<label for="PK_TICKET_CATEGORY">Ticket Category</label>
											</div>
										</div>
										
										<div class="col-md-3">
											<div class="form-group m-b-40">
												<select name="PK_TICKET_STATUS" id="PK_TICKET_STATUS" class="form-control required-entry" >
													<option value=""></option>
													<? $res_dep = $db->Execute("select PK_TICKET_STATUS,TICKET_STATUS from Z_TICKET_STATUS WHERE ACTIVE = '1' ORDER BY TICKET_STATUS ASC ");
													while (!$res_dep->EOF) {  ?>
														<option value="<?=$res_dep->fields['PK_TICKET_STATUS']?>" <? if($res_dep->fields['PK_TICKET_STATUS'] == 1) echo "selected"; ?> ><?=$res_dep->fields['TICKET_STATUS']?></option>
													<?	$res_dep->MoveNext();
													} 	?>
												</select>
												<span class="bar"></span>
												<label for="PK_TICKET_PRIORITY">Ticket Status</label>
											</div>
										</div>
										
										<div class="col-md-3">
											<div class="form-group m-b-40">
												<select name="PK_TICKET_PRIORITY" id="PK_TICKET_PRIORITY" class="form-control required-entry" >
													<option value=""></option>
													<? $res_dep = $db->Execute("select PK_TICKET_PRIORITY,TICKET_PRIORITY from Z_TICKET_PRIORITY WHERE ACTIVE = '1' ORDER BY PK_TICKET_PRIORITY ASC ");
													while (!$res_dep->EOF) {  ?>
														<option class="<?=$class?>" value="<?=$res_dep->fields['PK_TICKET_PRIORITY']?>" ><?=$res_dep->fields['TICKET_PRIORITY']?></option>
													<?	$res_dep->MoveNext();
													} 	?>
												</select>
												<span class="bar"></span>
												<label for="PK_TICKET_PRIORITY"><?=TICKET_PRIORITY?></label>
											</div>
										</div>
										
										<div class="col-md-3">
											<div class="form-group m-b-40">
												<input type="text" class="form-control date" id="DUE_DATE" name="DUE_DATE" value="<?=$DUE_DATE?>" >
												<span class="bar"></span>
												<label for="DUE_DATE"><?=DUE_DATE?></label>
											</div>
										</div>
										
									</div>
									<div class="row">
										<div class="col-md-3">
											<div class="form-group m-b-40">
												<select name="TICKET_FOR" id="TICKET_FOR" class="form-control " >
													<option value=""></option>
													<? $res_dep = $db->Execute("select PK_ACCOUNT,SCHOOL_NAME from Z_ACCOUNT WHERE ACTIVE = '1' AND PK_ACCOUNT != 1 ORDER BY SCHOOL_NAME ASC ");
													while (!$res_dep->EOF) {  ?>
														<option class="<?=$class?>" value="<?=$res_dep->fields['PK_ACCOUNT']?>" ><?=$res_dep->fields['SCHOOL_NAME']?></option>
													<?	$res_dep->MoveNext();
													} 	?>
												</select>
												<span class="bar"></span>
												<label for="TICKET_FOR">School Name</label>
											</div>
										</div>
										
										<div class="col-md-9">
											<div class="form-group m-b-40">
												<input type="text" class="form-control required-entry" id="SUBJECT" name="SUBJECT" value="<?=$SUBJECT?>" >
												<span class="bar"></span>
												<label for="SUBJECT"><?=SUBJECT?></label>
											</div>
										</div>
                                    </div>
									<? } else { ?>
									<div class="row">
                                        <div class="col-md-9" style="font-size: 16px;font-weight: bold;" >
											See Below the Textbox for Full Details of the Ticket
										</div>
									</div>
									<? } ?>
									<div class="row">
                                        <div class="col-md-9">
											<div class="form-group m-b-40">
												<textarea name="CONTENT" id="CONTENT"></textarea>
												<span class="bar"></span>
											</div>
										</div>
										<div class="col-md-3">
											<a href="javascript:void(0)" onclick="add_attachment()" ><b><?=ADD_ATTACHMENTS?></b></a>
											<div id="attachments_div"> </div>
										</div>
                                    </div>
								
									<div class="row">
                                        <div class="col-md-9">
											<div class="form-group m-b-5"  style="text-align:right" >
												<button type="submit" class="btn waves-effect waves-light btn-info"><?=SAVE?></button>
												<? if($_GET['id'] == '') 
													$URL = "manage_ticket";
												else
													$URL = "view_ticket?id=".$_GET['id']; ?>
												<button type="button" class="btn waves-effect waves-light btn-dark" onclick="window.location.href='<?=$URL?>'" ><?=CANCEL?></button>
												
											</div>
										</div>
									</div>
                                </form>
								
								<div class="row">
									<div class="col-12">
										<div class="card">
											<div class="card-body">
													<div class="row" >
														<div class="col-md-8">				
															<? $res = $db->Execute("select Z_TICKET.PK_TICKET, CONTENT, IF(Z_TICKET.CREATED_ON != '0000-00-00', DATE_FORMAT(Z_TICKET.CREATED_ON, '%m/%d/%Y %r'),'' ) AS CREATED_ON, CONCAT(S_EMPLOYEE_MASTER.FIRST_NAME ,' ',S_EMPLOYEE_MASTER.LAST_NAME) AS NAME, Z_TICKET.CREATED_BY from Z_TICKET LEFT JOIN Z_USER ON Z_USER.PK_USER = Z_TICKET.CREATED_BY LEFT JOIN S_EMPLOYEE_MASTER ON S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER = Z_USER.ID WHERE Z_TICKET.INTERNAL_ID = '$_GET[id]'  AND IS_DELETED = 0 ORDER BY Z_TICKET.PK_TICKET DESC "); 
															
															$i = 0;
															while (!$res->EOF) { 
																$PK_TICKET 		= $res->fields['PK_TICKET'];
																$PK_TICKET_A[] 	= $res->fields['PK_TICKET'];
																$style = '';
																if($i > 0)
																	$style = 'display:none;'; ?>
																<a href="javascript:void(0)" onclick="show_div(<?=$i?>)" ><h2 ><span><?=$res->fields['CREATED_ON']?></span></h2></a>
																<div id="content_div_<?=$i?>"  style="border:1px dashed #000; padding:25px;border-radius: 7px;<?=$style?>" >
																	<div class="form-group" >
																		<div class="col-lg-11">
																			<b><?=FROM?>: <?=$res->fields['NAME']?></b>
																		</div>
																	</div>
																	<hr />
																	
																	<div class="form-group">
																		<div class="col-lg-12">
																			<?=$res->fields['CONTENT']?>
																		</div>
																	</div>
																	<? $res_att = $db->Execute("SELECT * FROM Z_TICKET_ATTACHMENT WHERE PK_TICKET = '$PK_TICKET' AND ACTIVE = 1");
																	if($res_att->RecordCount() > 0){ ?>
																		<u><?=ATTACHMENTS?></u><br />
																		<? while (!$res_att->EOF) {  ?>
																			<a href="<?=$res_att->fields['LOCATION']?>" target="_blank" ><?=$res_att->fields['FILE_NAME']?></a><br />
																		<? $res_att->MoveNext();
																		} ?>
																	<? }
																	?>
																</div>
															<?	$i++;
																$res->MoveNext();
															}?>
														</div>	
														<div class="col-md-4">
															<h2 ><span><?=STATUS_HISTORY?></span></h2>
															
															<? $res_status = $db->Execute("SELECT TICKET_STATUS,CONCAT(S_EMPLOYEE_MASTER.FIRST_NAME,' ',S_EMPLOYEE_MASTER.LAST_NAME) AS NAME, CHANGED_ON FROM Z_TICKET_STATUS_CHANGE_HISTORY LEFT JOIN Z_USER ON Z_USER.PK_USER = CHANGED_BY LEFT JOIN S_EMPLOYEE_MASTER ON S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER = Z_USER.ID LEFT JOIN Z_TICKET_STATUS ON Z_TICKET_STATUS.PK_TICKET_STATUS = Z_TICKET_STATUS_CHANGE_HISTORY.PK_TICKET_STATUS WHERE INTERNAL_ID = '$_GET[id]' ORDER BY PK_TICKET_STATUS_CHANGE_HISTORY DESC ");
															while (!$res_status->EOF) { ?>
																<div class="form-group" >
																	<div class="col-lg-6">
																		<?=$res_status->fields['TICKET_STATUS']?><br />
																		<?=date('m/d/Y h:i A', strtotime($res_status->fields['CHANGED_ON'])); ?>
																	</div>
																	<div class="col-lg-6" style="text-align:right" >
																		<?=$res_status->fields['NAME']?>
																	</div>
																</div>
																<hr style="margin:5px;" />
															<?	$res_status->MoveNext();
															} ?>
															
															<h2 ><span><?=ALL_ATTACHMENTS?></span></h2>
															<? $PK_TICKETS = implode(',',$PK_TICKET_A);
															$res_att = $db->Execute("SELECT * FROM Z_TICKET_ATTACHMENT WHERE ACTIVE = 1 AND PK_TICKET IN ($PK_TICKETS) ORDER BY PK_TICKET_ATTACHMENT DESC ");
															while (!$res_att->EOF) { ?>
																<div class="form-group" >
																	<div class="col-lg-12">
																		<a href="<?=$res_att->fields['LOCATION']?>" target="_blank" ><?=$res_att->fields['FILE_NAME']?></a>
																	</div>
																
																	<div class="col-lg-12" style="text-align:right">
																		<?=date('m/d/Y h:i A', strtotime($res_att->fields['UPLOADED_ON'])); ?>
																	</div>
																</div>
															<?	$res_att->MoveNext();
															} ?>
														</div>
													</div>
											</div>
										</div>
									</div>
								</div>
								
                            </div>
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
	
	<script src="../backend_assets/dist/js/validation_prototype.js"></script>
	<script src="../backend_assets/dist/js/validation.js"></script>
	<script type="text/javascript">
		var form1 = new Validation('form1');
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
			
			jQuery('.date').datepicker({
				todayHighlight: true,
				orientation: "bottom auto"
			});
		});
		
		function add_attachment(){
			var name  =  'ATTACHMENT[]';
			var data  =  '<div class="form-group" >';
				data +=		'<div class="col-lg-2">&nbsp;</div>';
				data += 	'<div class="col-lg-8">';
				data += 	 	'<input type="file" name="'+name+'" multiple />';
				data += 	 '</div>';
				data += '</div>';
			jQuery(document).ready(function($) {
				$("#attachments_div").append(data);
			});
		}
		
		function show_div(id){
			jQuery(document).ready(function($) {
				$('#content_div_'+id).slideToggle(200);
			});
		}
	</script>
</body>

</html>
