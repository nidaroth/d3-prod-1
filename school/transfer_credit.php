<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/transfer_credit.php");

if($_SESSION['PK_USER'] == 0 || $_SESSION['PK_USER'] == '' || $_SESSION['PK_ROLES'] != 2 ){ 
	header("location:../index");  
	exit;
}

if(!empty($_POST)){
	//echo'<pre>';print_r($_POST);exit;
	/*$i = 0;
	foreach($_POST['COUNT'] as $COUNT) {  
		$TRANSFER_CREDIT 				  = array();
		$TRANSFER_CREDIT['ACTIVE'] 	  = $_POST['ACTIVE_'.$COUNT];
		$TRANSFER_CREDIT['LENDER'] 	  = $_POST['LENDER'][$i];
		$TRANSFER_CREDIT['CONTACT']     = $_POST['CONTACT'][$i];
		$TRANSFER_CREDIT['ADDRESS']     = $_POST['ADDRESS'][$i];
		$TRANSFER_CREDIT['ADDRESS1']    = $_POST['ADDRESS1'][$i];
		$TRANSFER_CREDIT['PHONE']  	  = $_POST['PHONE'][$i];
		$TRANSFER_CREDIT['EMAIL']  	  = $_POST['EMAIL'][$i];
		$TRANSFER_CREDIT['CITY']  	  = $_POST['CITY'][$i];
		$TRANSFER_CREDIT['ZIP']  		  = $_POST['ZIP'][$i];
		$TRANSFER_CREDIT['PK_STATES']   = $_POST['PK_STATES'][$i];
		
		if($_POST['PK_TRANSFER_CREDIT'][$i] == ''){
			$TRANSFER_CREDIT['PK_ACCOUNT']  = $_SESSION['PK_ACCOUNT'];
			$TRANSFER_CREDIT['CREATED_BY']  = $_SESSION['PK_USER'];
			$TRANSFER_CREDIT['CREATED_ON']  = date("Y-m-d H:i");
			db_perform('S_TRANSFER_CREDIT', $TRANSFER_CREDIT, 'insert');
			$PK_TRANSFER_CREDIT = $db->insert_ID;
		} 
		else{
			$PK_TRANSFER_CREDIT 			 = $_POST['PK_TRANSFER_CREDIT'][$i];
			$TRANSFER_CREDIT['EDITED_BY']  = $_SESSION['PK_USER'];
			$TRANSFER_CREDIT['EDITED_ON']  = date("Y-m-d H:i");
			db_perform('S_TRANSFER_CREDIT', $TRANSFER_CREDIT, 'update'," PK_TRANSFER_CREDIT = '$PK_TRANSFER_CREDIT' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'");
		}

		$PK_TRANSFER_CREDIT_ARR[] = $PK_TRANSFER_CREDIT;
		
		$i++;
	}

	$cond = '';
	if(!empty($PK_TRANSFER_CREDIT_ARR)){
		$cond = " AND PK_TRANSFER_CREDIT NOT IN (".implode(",",$PK_TRANSFER_CREDIT_ARR).")";
	}
	$db->Execute("DELETE from S_TRANSFER_CREDIT WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' $cond");
	
	header("location:transfer_credit");*/
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
	<title> <?=TRANSFER_CREDIT_PAGE_TITLE?> | <?=$title?></title>
</head>

<body class="horizontal-nav boxed skin-megna fixed-layout">
   <? require_once("pre_load.php"); ?>
    <div id="main-wrapper">
       <? require_once("menu.php"); ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                 <div class="row page-titles">
                    <div class="col-md-12 align-self-center">
                        <h4 class="text-themecolor"> <?=TRANSFER_CREDIT_PAGE_TITLE?> </h4>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <form class="floating-labels m-t-40" method="post" name="form1" id="form1" >
									
									<div id="form_div" >
									
										<? $fetch_fields_count = 1; 
										$result1 = $db->Execute("SELECT * FROM S_TRANSFER_CREDIT WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'");
										$reccnt  = $result1->RecordCount();
										while (!$result1->EOF) {
											$_REQUEST['PK_TRANSFER_CREDIT'] = $result1->fields['PK_TRANSFER_CREDIT'];
											$_REQUEST['count']  			= $fetch_fields_count;
											
											include('ajax_transfer_credit.php');
											
											$fetch_fields_count++;	
											$result1->MoveNext();
										} ?>
									</div>
									
									<div class="d-flex">
										<div class="col-md-10">&nbsp;</div>
										<div class="col-md-2 m-t-20 m-b-20 text-right">
											<button type="button" class="btn btn-primary" onClick="add_fields()" /><?=ADD?></button>
										</div>
									</div>
									
									<div class="row">
                                        <div class="col-md-7">
											<div class="form-group m-b-5"  style="text-align:right" >
												<button type="submit" class="btn waves-effect waves-light btn-info m-r-10"><?=SAVE?></button>
												
												<button type="button" class="btn waves-effect waves-light btn-dark m-r-10" onclick="window.location.href='setup'" ><?=CANCEL?></button>
												
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
   
	<? require_once("js.php"); ?>
	<script src="../backend_assets/dist/js/validation_prototype.js"></script>
	<script src="../backend_assets/dist/js/validation.js"></script>
	<script type="text/javascript">
		var form1 = new Validation('form1');
		jQuery(document).ready(function($) {
			if (jQuery(".lender-form").length == 0) {
				add_fields();
			}
		});
		
		var cunt = '<?=$fetch_fields_count?>';
		function add_fields(){
			jQuery(document).ready(function($) {
				var data = 'count='+cunt;
				var value = $.ajax({
					url: "ajax_transfer_credit",	
					type: "POST",
					data: data,		
					async: false,
					cache :false,
					success: function (data) {
						$("#form_div").append(data);
						
						$('.floating-labels .form-control').on('focus blur', function (e) {
							$(this).parents('.form-group').toggleClass('focused', (e.type === 'focus' || this.value.length > 0));
						}).trigger('blur');
						
						cunt++;
					}		
				}).responseText;
			});
		}
		
		function delete_row(id,type){
			jQuery(document).ready(function($) {
				if(type == 'detail')
					document.getElementById('delete_message').innerHTML = '<?=DELETE_MESSAGE_GENERAL?>?';
		
				$("#deleteModal").modal()
				$("#DELETE_ID").val(id)
				$("#DELETE_TYPE").val(type)
			});
		}
		
		function conf_delete(val,id){
			jQuery(document).ready(function($) {
				if(val == 1) {
					if($("#DELETE_TYPE").val() == 'detail'){
						var iid = $("#DELETE_ID").val()
						$("#table_"+iid).remove()
					}
				}
				$("#deleteModal").modal("hide");
			});
		}
		
		function get_country(val,cnt,id){ 
			jQuery(document).ready(function($) {   
				var data  = 'state='+val+'&id='+id;
				var value = $.ajax({
					url: "../super_admin/ajax_get_country_from_state",	
					type: "POST",		 
					data: data,		
					async: false,
					cache: false,
					success: function (data) {	
						document.getElementById(id).innerHTML = data;  
						document.getElementById('PK_COUNTRY_LABEL_'+cnt).classList.add("focused");
					}		
				}).responseText;
			});
		}
	</script>

</body>

</html>