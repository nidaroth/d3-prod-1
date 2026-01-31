<? require_once("../global/config.php");
require_once("../global/payments.php");
require_once("../school/function_student_ledger.php");
require_once("../school/function_update_disbursement_status.php");
require_once("../language/add-card.php");
require_once("../language/common.php");
require_once("../language/make_payment.php");

$res_pay = $db->Execute("select ENABLE_DIAMOND_PAY from Z_ACCOUNT WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
if($res_pay->fields['ENABLE_DIAMOND_PAY'] == 0) {
	header("location:../index");
	exit;
}

if($_SESSION['PK_USER'] == 0 || $_SESSION['PK_USER'] == '' || $_SESSION['PK_USER_TYPE'] != 3 ){
	header("location:../index");
	exit;
}

if($_GET['act'] == 'del_cc'){
	$db->Execute("DELETE from S_STUDENT_CREDIT_CARD WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_MASTER = '$_SESSION[PK_STUDENT_MASTER]' AND PK_STUDENT_CREDIT_CARD = '$_GET[iid]' ");
	header("location:payment_info.php?id=".$_GET['id']."&page=".$_GET['id']);
	exit;
} else if($_GET['act'] == 'pri'){
	$db->Execute("UPDATE S_STUDENT_CREDIT_CARD SET IS_PRIMARY = 0 WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_MASTER = '$_SESSION[PK_STUDENT_MASTER]' ");
	$db->Execute("UPDATE S_STUDENT_CREDIT_CARD SET IS_PRIMARY = 1 WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_CREDIT_CARD = '$_GET[iid]' AND PK_STUDENT_MASTER = '$_SESSION[PK_STUDENT_MASTER]' ");
	header("location:payment_info.php?id=".$_GET['id']."&page=".$_GET['page']);
	exit;
}

$URL = 'payments';

$msg = "";
if(!empty($_POST)){
	//echo "<pre>";print_r($_POST);exit;		
}
$res_card = $db->Execute("select * from S_STUDENT_CREDIT_CARD WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_MASTER = '$_SESSION[PK_STUDENT_MASTER]' ");

$res = $db->Execute("select * from S_CARD_X_SETTINGS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
$PUBLISHER_NAME 	= $res->fields['PUBLISHER_NAME'];
$PUBLISHER_PASSWORD = $res->fields['PUBLISHER_PASSWORD'];
$SITE_KEY 			= $res->fields['SITE_KEY'];
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
	<title><?=PAYMENT_INFO ?> | <?=$title?></title>
</head>

<body class="horizontal-nav boxed skin-megna fixed-layout">
   <? require_once("pre_load.php"); ?>
    <div id="main-wrapper">
       <? require_once("menu.php"); ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                 <div class="row page-titles">
					<div class="col-md-10 align-self-center">
                        <h4 class="text-themecolor"><?=CARD_INFO?></h4>
                    </div>
					<div class="col-md-2 text-right">
						<a href="add_cc" class="btn btn-info d-none d-lg-block m-l-15"><i class="fa fa-plus-circle"></i> <?=CREATE_NEW?></a>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="card">
							<form class="floating-labels m-t-40" method="post" name="form1" id="form1" enctype="multipart/form-data" >
								
								<div class="p-20">
									<div class="row">
										<div class="col-12">
											<? $res_type = $db->Execute("select * from S_STUDENT_CREDIT_CARD WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_MASTER = '$_SESSION[PK_STUDENT_MASTER]' ORDER BY PK_STUDENT_CREDIT_CARD DESC");
											if($res_type->RecordCount() > 0){ ?>
											<table class="table table-hover">
												<thead>
													<tr>
														<th><?=NAME_ON_CARD?></th>
														<th><?=CARD_NO?></th>
														<th><?=CARD_EXP?></th>
														<th><?=CARD_TYPE?></th>
														<th><?=IS_PRIMARY?></th>
														<th><?=OPTIONS?></th>
													</tr>
												</thead>
												<tbody>
												<? while (!$res_type->EOF) { ?>
													<tr>
														<td><?=$res_type->fields['NAME_ON_CARD']?></td>
														<td><?=$res_type->fields['CARD_NO']?></td>
														<td><?=$res_type->fields['CARD_EXP']?></td>
														<td><?=$res_type->fields['CARD_TYPE']?></td>
														<td>
															<? if($res_type->fields['IS_PRIMARY'] == 1) 
																echo "Yes"; 
															else {
																echo "No<br />"; ?>
																<a href="javascript:void(0);" onclick="set_as_primary('<?=$res_type->fields['PK_STUDENT_CREDIT_CARD']?>')" title="<?=SET_AS_PRIMARY?>" ><?=SET_AS_PRIMARY?></a>
															<? } ?>
														</td>
														<td>
															<a href="javascript:void(0);" onclick="delete_card_popup('<?=$res_type->fields['PK_STUDENT_CREDIT_CARD']?>')" title="<?=DELETE_CARD?>" class="btn delete-color btn-circle"><i class="far fa-trash-alt"></i> </a>
														</td>
													</tr>
												<?	$res_type->MoveNext();
												} ?>
												</tbody>
											</table>
											<? } else { ?>
												No Credit Card On File
											<? } ?>
										</div>
									</div>
									
								</div>
							</form>
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
						<h4 class="modal-title" id="exampleModalLabel1"><?=CONFIRMATION?></h4>
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					</div>
					<div class="modal-body">
						<div class="form-group">
							<?=DELETE_CONFIRMATION_MSG ?>
						</div>
					</div>
					<div class="modal-footer">
						<input type="hidden" id="DELETE_ID" value="" >
						<button type="button" onclick="conf_delete_card_popup(1)" class="btn waves-effect waves-light btn-info"><?=YES?></button>
						<button type="button" class="btn waves-effect waves-light btn-dark" onclick="conf_delete_card_popup(0)" ><?=NO?></button>
					</div>
				</div>
			</div>
		</div>
		
    </div>
   
	<? require_once("js.php"); ?>

	<script language="javascript">
	function delete_card_popup(id){
		jQuery(document).ready(function($) {
			$("#deleteModal").modal()
			$("#DELETE_ID").val(id)
		});
	}
	function conf_delete_card_popup(val){
		jQuery(document).ready(function($) {
			if(val == 1) {
				window.location.href = "payment_info.php?id=<?=$_GET['id']?>&page=<?=$_GET['page']?>&act=del_cc&iid="+$("#DELETE_ID").val();
			}
			$("#deleteModal").modal("hide");
		});
	}
	function set_as_primary(id){
		window.location.href = "payment_info.php?id=<?=$_GET['id']?>&page=<?=$_GET['page']?>&act=pri&iid="+id;
	}
	</script>
	
</body>

</html>