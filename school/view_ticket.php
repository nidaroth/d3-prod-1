<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/ticket.php");

if($_SESSION['PK_USER'] == 0 || $_SESSION['PK_USER'] == ''){ 
	header("location:../signin?p=view_ticket&id=".$_GET['id']);
	exit;
}
require_once('send_notification.php');
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
       <? require_once("menu.php"); 
	   $ticket_cond = " AND (Z_TICKET.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' OR Z_TICKET.PK_ACCOUNT = '-1') ";
		
		$res = $db->Execute("select SUBJECT,TICKET_PRIORITY,TICKET_STATUS,TICKET_NO, Z_TICKET.PK_TICKET_STATUS, Z_TICKET.PK_TICKET_PRIORITY, CLOSED_DATE, DUE_DATE from Z_TICKET LEFT JOIN Z_TICKET_STATUS ON Z_TICKET_STATUS.PK_TICKET_STATUS = Z_TICKET.PK_TICKET_STATUS LEFT JOIN Z_TICKET_PRIORITY on Z_TICKET.PK_TICKET_PRIORITY = Z_TICKET_PRIORITY.PK_TICKET_PRIORITY WHERE PK_TICKET = '$_GET[id]' $ticket_cond "); 
		
		if($res->RecordCount() == 0){
			header("location:manage_ticket");
			exit;
		} ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                <div class="row page-titles">
                    <div class="col-md-2 align-self-center">
                        <h4 class="text-themecolor"><?=VIEW_TICKET?> # <?=$res->fields['TICKET_NO']?></h4>
                    </div>
					<div class="col-md-4 align-self-center">
						<h4 class="text-themecolor"><?=SUBJECT?>: <?=$res->fields['SUBJECT']?></h4>
					</div>
					<div class="col-md-2 align-self-center">
						<h4 class="text-themecolor"><?=PRIORITY?>: <?=$res->fields['TICKET_PRIORITY']?></h4>
					</div>
					
					<div class="col-md-2">
						<h4 class="text-themecolor"><?=CLOSE_DATE?>: <? if($res->fields['CLOSED_DATE'] != '0000-00-00') echo date("m/d/Y",strtotime($res->fields['CLOSED_DATE'])); ?></h4>
					</div>
					
					<div class="col-md-2 align-self-center">
						<button type="button" class="btn waves-effect waves-light btn-dark" onclick="window.location.href='manage_ticket'"><?=BACK?></button>
						
						<button type="button" class="btn waves-effect waves-light btn-dark" onclick="window.location.href='ticket?id=<?=$_GET['id']?>'"><?=REPLY?></button>
					</div>
                </div>
				<div class="row">
					<div class="col-md-1">
						<h4 class="text-themecolor"><?=CLOSE_DATE?>:</h4>
					</div>
					<div class="col-md-5">
						<div id="PK_TICKET_STATUS_DIV_1">
							<?=$res->fields['TICKET_STATUS']?>&nbsp;
							<a href="javascript:void(0)" onclick="change_date('PK_TICKET_STATUS',1)" ><?=CHANGE_STATUS?></a>
						</div>
									
						<div style="display:none;" id="PK_TICKET_STATUS_DIV_2">
							<div style="width:34%;margin-right:5%;float:left" >
								<select name="PK_TICKET_STATUS" id="PK_TICKET_STATUS" class="form-control required-entry" >
									<? $res_dep = $db->Execute("select PK_TICKET_STATUS,TICKET_STATUS from Z_TICKET_STATUS WHERE ACTIVE = '1' ORDER BY TICKET_STATUS ASC ");
									while (!$res_dep->EOF) { ?>
										<option value="<?=$res_dep->fields['PK_TICKET_STATUS']?>" <? if($res_dep->fields['PK_TICKET_STATUS'] == $res->fields['PK_TICKET_STATUS']) echo "selected"; ?> ><?=$res_dep->fields['TICKET_STATUS']?></option>
									<?	$res_dep->MoveNext();
									} 	?>
								</select>
							</div>
							<div style="width:20%;float:left" >
								<button type="button" class="btn btn-primary " onClick="save_status()" ><?=SAVE?></button>
							</div>
							<div style="width:20%;float:left;margin-top:5px;" >
								<a href="javascript:void(0)" onclick="change_date('PK_TICKET_STATUS',2)" ><?=CANCEL?></a>
							</div>
						</div>
					</div>
					
					<div class="col-md-1 align-self-center">
						<h4 class="text-themecolor"><?=PRIORITY?>: </h4>
					</div>
					<div class="col-md-3">
						<div id="PK_TICKET_PRIORITY_DIV_1" >
							<?=$res->fields['TICKET_PRIORITY']?>&nbsp;
							<a href="javascript:void(0)" onclick="change_date('PK_TICKET_PRIORITY',1)" ><?=CHANGE_STATUS?></a>
						</div>
									
						<div style="display:none;" id="PK_TICKET_PRIORITY_DIV_2">
							<div style="width:55%;margin-right:5%;float:left" >
								<select name="PK_TICKET_PRIORITY" id="PK_TICKET_PRIORITY" class="form-control required-entry" >
									<? $res_dep = $db->Execute("select PK_TICKET_PRIORITY,TICKET_PRIORITY from Z_TICKET_PRIORITY WHERE ACTIVE = '1' ORDER BY TICKET_PRIORITY ASC ");
									while (!$res_dep->EOF) { ?>
										<option value="<?=$res_dep->fields['PK_TICKET_PRIORITY']?>" <? if($res_dep->fields['PK_TICKET_PRIORITY'] == $res->fields['PK_TICKET_PRIORITY']) echo "selected"; ?> ><?=$res_dep->fields['TICKET_PRIORITY']?></option>
									<?	$res_dep->MoveNext();
									} 	?>
								</select>
							</div>
							<div style="width:20%;float:left" >
								<button type="button" class="btn btn-primary " onClick="save_priority()" ><?=SAVE?></button>
							</div>
							<div style="width:20%;float:left;margin-top:5px;" >
								<a href="javascript:void(0)" onclick="change_date('PK_TICKET_PRIORITY',2)" ><?=CANCEL?></a>
							</div>
						</div>
					</div>
					<div class="col-md-2 align-self-center">
						<h4 class="text-themecolor"><?=DUE_DATE?>: 
							<? if($res->fields['DUE_DATE'] != '0000-00-00') echo date("m/d/Y",strtotime($res->fields['DUE_DATE'])); ?>
						</h4>
					</div>
				</div>
									
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <form class="floating-labels m-t-40" method="post" name="form1" id="form1" >
									<div class="row" >
										<div class="col-md-8">				
											<? $res = $db->Execute("select Z_TICKET.PK_TICKET, CONTENT, IF(Z_TICKET.CREATED_ON != '0000-00-00', DATE_FORMAT(Z_TICKET.CREATED_ON, '%m/%d/%Y %r'),'' ) AS CREATED_ON, CONCAT(S_EMPLOYEE_MASTER.FIRST_NAME ,' ',S_EMPLOYEE_MASTER.LAST_NAME) AS NAME, Z_TICKET.CREATED_BY from Z_TICKET, Z_USER LEFT JOIN S_EMPLOYEE_MASTER ON S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER = Z_USER.ID WHERE Z_TICKET.INTERNAL_ID = '$_GET[id]' AND Z_USER.PK_USER = Z_TICKET.CREATED_BY $ticket_cond  AND IS_DELETED = 0 ORDER BY Z_TICKET.PK_TICKET DESC "); 
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
												<div class="row" >
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
		
		var frmValidate = new VarienForm('form1');
			function show_div(id){
				jQuery(document).ready(function($) {
					$('#content_div_'+id).slideToggle(200);
				});
			}
			function save_status(){
				jQuery(document).ready(function($) {
					var data = 'PK_TICKET_STATUS='+document.getElementById('PK_TICKET_STATUS').value+'&INTERNAL_ID=<?=$_GET['id']?>';
					//alert(data);
					var value = $.ajax({
						url: "ajax_change_ticket_status",	
						type: "POST",		 
						data: data,		
						async: false,
						cache :false,
						success: function (data) {//alert(data);
							location.reload(); 
						}		
					}).responseText;
				})
			}
			function change_date(id,val){
				if(val == 1){
					document.getElementById(id+'_DIV_1').style.display = 'none';
					document.getElementById(id+'_DIV_2').style.display = 'block';
				} else {
					document.getElementById(id+'_DIV_1').style.display = 'block';
					document.getElementById(id+'_DIV_2').style.display = 'none';
				}
			}
			function save_priority(){
				jQuery(document).ready(function($) {
					var data = 'PK_TICKET_PRIORITY='+document.getElementById('PK_TICKET_PRIORITY').value+'&INTERNAL_ID=<?=$_GET['id']?>';
					//alert(data);
					var value = $.ajax({
						url: "ajax_change_priority",	
						type: "POST",		 
						data: data,		
						async: false,
						cache :false,
						success: function (data) {
							//alert(data);
							location.reload(); 
						}		
					}).responseText;
				})
			}
	</script>

</body>

</html>