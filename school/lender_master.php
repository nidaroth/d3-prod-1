<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/lender_master.php");
require_once("check_access.php");

if(check_access('SETUP_FINANCE') == 0 ){
	header("location:../index");
	exit;
}

if(!empty($_POST)){
	//echo'<pre>';print_r($_POST);exit;
	$i = 0;
	foreach($_POST['COUNT'] as $COUNT) {  
		$LENDER_MASTER 				  = array();
		$LENDER_MASTER['ACTIVE'] 	  = $_POST['ACTIVE_'.$COUNT];
		$LENDER_MASTER['LENDER'] 	  = $_POST['LENDER'][$i];
		$LENDER_MASTER['CONTACT']     = $_POST['CONTACT'][$i];
		$LENDER_MASTER['ADDRESS']     = $_POST['ADDRESS'][$i];
		$LENDER_MASTER['ADDRESS1']    = $_POST['ADDRESS1'][$i];
		$LENDER_MASTER['PHONE']  	  = $_POST['PHONE'][$i];
		$LENDER_MASTER['EMAIL']  	  = $_POST['EMAIL'][$i];
		$LENDER_MASTER['CITY']  	  = $_POST['CITY'][$i];
		$LENDER_MASTER['ZIP']  		  = $_POST['ZIP'][$i];
		$LENDER_MASTER['PK_STATES']   = $_POST['PK_STATES'][$i];
		
		if($_POST['PK_LENDER_MASTER'][$i] == ''){
			$LENDER_MASTER['PK_ACCOUNT']  = $_SESSION['PK_ACCOUNT'];
			$LENDER_MASTER['CREATED_BY']  = $_SESSION['PK_USER'];
			$LENDER_MASTER['CREATED_ON']  = date("Y-m-d H:i");
			db_perform('S_LENDER_MASTER', $LENDER_MASTER, 'insert');
			$PK_LENDER_MASTER = $db->insert_ID;
		} 
		else{
			$PK_LENDER_MASTER 			 = $_POST['PK_LENDER_MASTER'][$i];
			$LENDER_MASTER['EDITED_BY']  = $_SESSION['PK_USER'];
			$LENDER_MASTER['EDITED_ON']  = date("Y-m-d H:i");
			db_perform('S_LENDER_MASTER', $LENDER_MASTER, 'update'," PK_LENDER_MASTER = '$PK_LENDER_MASTER' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'");
		}

		$PK_LENDER_MASTER_ARR[] = $PK_LENDER_MASTER;
		
		$i++;
	}

	$cond = '';
	if(!empty($PK_LENDER_MASTER_ARR)){
		$cond = " AND PK_LENDER_MASTER NOT IN (".implode(",",$PK_LENDER_MASTER_ARR).")";
	}
	$db->Execute("DELETE from S_LENDER_MASTER WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' $cond");
	
	header("location:lender_master");
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
	<title> <?=LENDER_MASTER_PAGE_TITLE?> | <?=$title?></title>
</head>

<body class="horizontal-nav boxed skin-megna fixed-layout">
   <? require_once("pre_load.php"); ?>
    <div id="main-wrapper">
       <? require_once("menu.php"); ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                 <div class="row page-titles">
                    <div class="col-md-12 align-self-center">
                        <h4 class="text-themecolor"> <?=LENDER_MASTER_PAGE_TITLE?> </h4>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <form class="floating-labels m-t-40" method="post" name="form1" id="form1" >
									
									<div id="form_div" >
									
										<? $fetch_fields_count = 1; 
										$result1 = $db->Execute("SELECT * FROM S_LENDER_MASTER WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'");
										$reccnt  = $result1->RecordCount();
										while (!$result1->EOF) {
											$_REQUEST['PK_LENDER_MASTER'] 	= $result1->fields['PK_LENDER_MASTER'];
											$_REQUEST['count']  			= $fetch_fields_count;
											
											include('ajax_lender_master.php');
											
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
					url: "ajax_lender_master",	
					type: "POST",
					data: data,		
					async: false,
					cache :false,
					success: function (data) {
						$("#form_div").append(data);
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