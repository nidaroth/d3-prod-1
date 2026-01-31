<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/mail.php");

if($_SESSION['PK_USER'] == 0 || $_SESSION['PK_USER'] == ''){ 
	header("location:../index");
	exit;
}

if($_GET['act'] == 'i'){
	$db->Execute("UPDATE Z_INTERNAL_EMAIL_RECEPTION SET DELETED = 0 WHERE INTERNAL_ID = '$_GET[id]' AND PK_USER = '$_SESSION[PK_USER]' "); 
	header("location:my_mails.php?type=".$_GET['type']);
}

if($_GET['act'] == 'update'){
	if($_GET['iid'] == 1)
		$VIWED = 1;
	else
		$VIWED = 0;

	$db->Execute("UPDATE Z_INTERNAL_EMAIL_RECEPTION SET VIWED = '$VIWED' WHERE INTERNAL_ID = '$_GET[id]' AND PK_USER = '$_SESSION[PK_USER]' ");
		
	header("location:my_mails?type".$_GET['type']);	
	exit;
}
$res_pk   = $db->Execute("select PK_INTERNAL_EMAIL from Z_INTERNAL_EMAIL WHERE INTERNAL_ID = '$_GET[id]' ORDER BY PK_INTERNAL_EMAIL DESC"); 
$PK_INTERNAL_EMAIL = $res_pk->fields['PK_INTERNAL_EMAIL'];

$res = $db->Execute("select SUBJECT from Z_INTERNAL_EMAIL WHERE PK_INTERNAL_EMAIL = '$_GET[id]' "); 
$db->Execute("UPDATE Z_INTERNAL_EMAIL_RECEPTION SET VIWED = 1 WHERE INTERNAL_ID = '$_GET[id]' AND PK_USER = '$_SESSION[PK_USER]' "); 

$res_att = $db->Execute("SELECT * FROM Z_INTERNAL_EMAIL_STARRED WHERE INTERNAL_ID = '$_GET[id]' AND STARRED = 1 AND PK_USER = '$_SESSION[PK_USER]' ")or die(mysql_error());
if($res_att->RecordCount() > 0)
	$color = 'gold';
else
	$color = '#DDDDDD';
	
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
		<?=EMAIL_TITLE ?>
	</title>
	<link rel="stylesheet" type="text/css" href="../backend_assets/dist/css/pages/inbox.css">
	<style>
		h2 { width:100%; text-align:center; border-bottom: 1px solid #000; line-height:0.1em; margin:10px 0 20px; font-size:15px;padding-top:10px;} 
		h2 span { background:#fff; padding:0 10px; }
	</style>
</head>

<body class="horizontal-nav boxed skin-megna fixed-layout">
	<? require_once("pre_load.php"); ?>
    <div id="main-wrapper">
        <? require_once("menu.php"); ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="row">
                                <div class="col-xlg-2 col-lg-3 col-md-4">
                                    <? include('mail_left_menu.php') ?>
                                </div>
                                <div class="col-xlg-10 col-lg-9 col-md-8 bg-light border-left">
                                    <div class="card-body">
                                        <div class="btn-group m-b-10 m-r-10" role="group" aria-label="Button group with nested dropdown">
                                            <button type="button" class="btn btn-secondary font-18"><i class="fa fa-star" id="star_id" onclick="star(<?=$_GET['id']?>)" style="color:<?=$color?>" ></i></button>
                                        </div>
										
										<!-- <div class="btn-group m-b-10 m-r-10" role="group" aria-label="Button group with nested dropdown">
                                            <button type="button" class="btn btn-secondary font-18" onclick="delete_row(<?=$PK_INTERNAL_EMAIL?>)" ><i class="mdi mdi-delete"></i></button>
                                        </div> -->
                                        
                                        <div class="btn-group m-b-10 m-r-10" role="group" aria-label="Button group with nested dropdown">
											 <button type="button" class="btn btn-secondary font-18" onclick="change_mail_status(2)" ><?=MARK_AS_UNREAD?></button>
                                        </div>
										
										<div class="btn-group m-b-10 m-r-10" role="group" aria-label="Button group with nested dropdown">
                                            <button type="button" class="btn btn-secondary font-18" onclick="window.location.href='my_mails?type=<?=$_GET['type']?>'" >Back</button>
                                        </div>
                                    </div>
                                    <div class="card-body p-t-0">
                                        <div class="card b-all shadow-none">
                                            <div class="card-body">
                                                <h4 class="card-title m-b-0"><?=$res->fields['SUBJECT']?></h4>
                                            </div>
                                            <div>
                                                <hr class="m-t-0">
                                            </div>
                                            <div class="card-body">
												<? if($_GET['type'] == 'sent'){
													$res = $db->Execute("select Z_INTERNAL_EMAIL.PK_INTERNAL_EMAIL, IF(PK_USER_TYPE = 2, CONCAT(S_EMPLOYEE_MASTER.FIRST_NAME,' ',S_EMPLOYEE_MASTER.LAST_NAME) , IF(PK_USER_TYPE = 3, CONCAT(S_STUDENT_MASTER.FIRST_NAME,' ',S_STUDENT_MASTER.LAST_NAME) , IF(PK_USER_TYPE = 1, CONCAT(S_EMPLOYEE_MASTER.FIRST_NAME,' ',S_EMPLOYEE_MASTER.LAST_NAME),'') )) AS NAME, CONTENT,IF(REMINDER_DATE != '0000-00-00', DATE_FORMAT(REMINDER_DATE, '%m/%d/%Y'),'' ) AS REMINDER_DATE ,IF(DUE_DATE != '0000-00-00', DATE_FORMAT(DUE_DATE, '%m/%d/%Y'),'' ) AS DUE_DATE, IF(Z_INTERNAL_EMAIL.CREATED_ON != '0000-00-00', DATE_FORMAT(Z_INTERNAL_EMAIL.CREATED_ON, '%m/%d/%Y %r'),'' ) AS CREATED_ON, IF(PK_USER_TYPE = 2, S_EMPLOYEE_MASTER.IMAGE , IF(PK_USER_TYPE = 3, S_STUDENT_MASTER.IMAGE , IF(PK_USER_TYPE = 1, S_EMPLOYEE_MASTER.IMAGE,'') )) AS IMAGE 
													from 
													Z_INTERNAL_EMAIL, 
													Z_USER 
													LEFT JOIN S_EMPLOYEE_MASTER ON S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER = Z_USER.ID AND PK_USER_TYPE IN (1,2)  
													LEFT JOIN S_STUDENT_MASTER ON S_STUDENT_MASTER.PK_STUDENT_MASTER = Z_USER.ID AND PK_USER_TYPE = 3 
													WHERE 
													Z_INTERNAL_EMAIL.INTERNAL_ID = '$_GET[id]' AND 
													Z_INTERNAL_EMAIL.CREATED_BY = '$_SESSION[PK_USER]' AND 
													Z_USER.PK_USER = Z_INTERNAL_EMAIL.CREATED_BY 
													ORDER BY Z_INTERNAL_EMAIL.CREATED_ON DESC"); 
													
												} else {
													$res = $db->Execute("select PK_INTERNAL_EMAIL_RECEPTION,Z_INTERNAL_EMAIL.PK_INTERNAL_EMAIL, CONTENT,IF(REMINDER_DATE != '0000-00-00', DATE_FORMAT(REMINDER_DATE, '%m/%d/%Y'),'' ) AS REMINDER_DATE, IF(PK_USER_TYPE = 2, CONCAT(S_EMPLOYEE_MASTER.FIRST_NAME,' ',S_EMPLOYEE_MASTER.LAST_NAME) , IF(PK_USER_TYPE = 3, CONCAT(S_STUDENT_MASTER.FIRST_NAME,' ',S_STUDENT_MASTER.LAST_NAME) , IF(PK_USER_TYPE = 1, CONCAT(S_EMPLOYEE_MASTER.FIRST_NAME,' ',S_EMPLOYEE_MASTER.LAST_NAME),'') )) AS NAME ,IF(DUE_DATE != '0000-00-00', DATE_FORMAT(DUE_DATE, '%m/%d/%Y'),'' ) AS DUE_DATE, IF(Z_INTERNAL_EMAIL_RECEPTION.CREATED_ON != '0000-00-00', DATE_FORMAT(Z_INTERNAL_EMAIL_RECEPTION.CREATED_ON, '%m/%d/%Y %r'),'' ) AS CREATED_ON, IF(PK_USER_TYPE = 2, S_EMPLOYEE_MASTER.IMAGE , IF(PK_USER_TYPE = 3, S_STUDENT_MASTER.IMAGE , IF(PK_USER_TYPE = 1, S_EMPLOYEE_MASTER.IMAGE,'') )) AS IMAGE 
													from 
													Z_INTERNAL_EMAIL, 
													Z_INTERNAL_EMAIL_RECEPTION, 
													Z_USER 
													LEFT JOIN S_EMPLOYEE_MASTER ON S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER = Z_USER.ID AND PK_USER_TYPE IN (1,2)  
													LEFT JOIN S_STUDENT_MASTER ON S_STUDENT_MASTER.PK_STUDENT_MASTER = Z_USER.ID AND PK_USER_TYPE = 3 
													WHERE 
													Z_INTERNAL_EMAIL.INTERNAL_ID = '$_GET[id]' AND 
													Z_INTERNAL_EMAIL_RECEPTION.PK_INTERNAL_EMAIL = Z_INTERNAL_EMAIL.PK_INTERNAL_EMAIL AND 
													Z_INTERNAL_EMAIL_RECEPTION.PK_USER = '$_SESSION[PK_USER]' AND 
													Z_USER.PK_USER = Z_INTERNAL_EMAIL.CREATED_BY 
													ORDER BY PK_INTERNAL_EMAIL_RECEPTION DESC "); 
												}
												$i = 0;
												while (!$res->EOF) { 
													$PK_INTERNAL_EMAIL = $res->fields['PK_INTERNAL_EMAIL'];
													$style = '';
													if($i > 0)
														$style = 'display:none;'; ?>
														
													<h2 onclick="show_div(<?=$i?>)" ><span><?=$res->fields['CREATED_ON']?></span></h2>
													<div id="content_div_<?=$i?>"  style="border:1px dashed #000; padding:5px;border-radius: 7px;<?=$style?>" >
														<div class="d-flex m-b-40">
															<? if($res->fields['IMAGE'] != ''){ ?>
															<div>
																<a href="javascript:void(0)">
																	<img src="<?=$res->fields['IMAGE']?>" alt="user" width="40" class="img-circle" />
																</a>
															</div>
															<? } ?>
															<div class="p-l-10">
																<b ><?=$res->fields['NAME']?></b>
															</div>
														</div>
														<div class="d-flex m-b-40">
															<div class="p-l-10">
																<b>To: 
																<? $k = 0;
																$res_rep = $db->Execute("SELECT IF(PK_USER_TYPE = 2, CONCAT(S_EMPLOYEE_MASTER.FIRST_NAME,' ',S_EMPLOYEE_MASTER.LAST_NAME) , IF(PK_USER_TYPE = 3, CONCAT(S_STUDENT_MASTER.FIRST_NAME,' ',S_STUDENT_MASTER.LAST_NAME) , IF(PK_USER_TYPE = 1, CONCAT(S_EMPLOYEE_MASTER.FIRST_NAME,' ',S_EMPLOYEE_MASTER.LAST_NAME),'') )) AS NAME, PK_USER_TYPE, ID  
																FROM 
																Z_INTERNAL_EMAIL_RECEPTION,
																Z_USER 
																LEFT JOIN S_EMPLOYEE_MASTER ON S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER = Z_USER.ID AND PK_USER_TYPE IN (1,2)  
																LEFT JOIN S_STUDENT_MASTER ON S_STUDENT_MASTER.PK_STUDENT_MASTER = Z_USER.ID AND PK_USER_TYPE = 3 
																WHERE 
																Z_INTERNAL_EMAIL_RECEPTION.PK_USER = Z_USER.PK_USER AND 
																PK_INTERNAL_EMAIL = '$PK_INTERNAL_EMAIL' ");
																while (!$res_rep->EOF) { 
																	if($k > 0)
																		echo ", ";
																	echo $res_rep->fields['NAME'];
																	
																	$res_rep->MoveNext();
																	$k++;
																} ?>
																</b>
															</div>
														</div>
														<p><?=$res->fields['CONTENT']?></p>
											
														<? $res_att = $db->Execute("SELECT * FROM Z_INTERNAL_EMAIL_ATTACHMENT WHERE PK_INTERNAL_EMAIL = '$PK_INTERNAL_EMAIL' AND ACTIVE = 1");
														if($res_att->RecordCount() > 0){ ?>
															<h4><i class="fa fa-paperclip m-r-10 m-b-10"></i> <?=ATTACHMENT?> <span>(<?=$res_att->RecordCount()?>)</span></h4>
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
                                           
                                            <div class="card-body">
                                                <div class="b-all m-t-20 p-20">
                                                   
												   <? if($_GET['type'] != 'trash') { ?>
												   <button type="button" class="btn waves-effect waves-light btn-dark" onclick="window.location.href='compose_mail?id=<?=$_GET['id']?>&pk=<?=$PK_INTERNAL_EMAIL?>&type=reply'" ><?=REPLY?></button>
												   
												   <button type="button" class="btn waves-effect waves-light btn-dark" onclick="window.location.href='compose_mail?id=<?=$_GET['id']?>&pk=<?=$PK_INTERNAL_EMAIL?>&type=forward'" ><?=FORWARD?></button>
												   <? } ?>
												   
												   <? if($_GET['type'] == 'trash') { ?>
													<button style="float:right;;margin-right:5px;" type="button" class="btn waves-effect waves-light btn-dark" onclick="window.location.href='email.php?type=trash&id=<?=$_GET['id']?>&act=i'"><?=MOVE_TO_INVOICE?></button>
													<? } ?>
												   
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
		
		<div class="modal" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel1">
			<div class="modal-dialog" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<h4 class="modal-title" id="exampleModalLabel1"><?=DELETE_CONFIRMATION?></h4>
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					</div>
					<div class="modal-body">
						<div class="form-group">
							<?=DELETE_MESSAGE_MAIL ?>
							<input type="hidden" id="DELETE_ID" value="0" />
							
						</div>
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
   
   <script type="text/javascript">
		function show_div(id){
			jQuery(document).ready(function($) {
				$('#content_div_'+id).slideToggle(200);
			});
			/*if(document.getElementById('content_div_'+id).style.display == 'block')
				document.getElementById('content_div_'+id).style.display = 'none';
			else
				document.getElementById('content_div_'+id).style.display = 'block';*/
		}
		function star(id){
			jQuery(document).ready(function($) { 
				var data  = 'id='+id;
				var value = $.ajax({
					url: "../school/set_stared",	
					type: "POST",		 
					data: data,		
					async: false,
					cache: false,
					success: function (data) {	
						//alert(data)
						document.getElementById('star_id').style.color = data;
					}		
				}).responseText;
			});
		}
		
		function delete_row(id,type){
			jQuery(document).ready(function($) {
				$("#deleteModal").modal()
				$("#DELETE_ID").val(id)
			});
		}
		function conf_delete(val,id){
			if(val == 1){
				window.location.href = 'my_mails.php?type=<?=$_GET['type']?>&act=del&id='+$("#DELETE_ID").val();
			} else
				$("#deleteModal").modal("hide");
		}
		function change_mail_status(type){
			window.location.href = 'email.php?type=<?=$_GET['type']?>&act=update&iid='+type+'&id=<?=$_GET['id']?>';
		}
	</script>
</body>

</html>