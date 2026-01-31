<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/earnings_setup.php");
require_once("../language/menu.php");
require_once("check_access.php");

if(check_access('MANAGEMENT_ACCOUNTING') == 0 ){
	header("location:../index");
	exit;
}
if(!empty($_POST)){
	//echo "<pre>";print_r($_POST);exit;
	
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
	<title><?=MNU_EARNINGS_CALCULATION ?> | <?=$title?></title>
	<style>
		li > a > label{position: unset !important;}
	</style>
</head>

<body class="horizontal-nav boxed skin-megna fixed-layout">
   <? require_once("pre_load.php"); ?>
    <div id="main-wrapper">
       <? require_once("menu.php"); ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                 <div class="row page-titles">
                    <div class="col-md-5 align-self-center">
                        <h4 class="text-themecolor"><?=MNU_MONTHLY_EARNINGS_CALCULATION ?></h4>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="card">
							<form class="floating-labels m-t-40" method="post" name="form1" id="form1" enctype="multipart/form-data" >
								<div class="p-20">
									<div class="d-flex">
										<div class="col-12 col-sm-12 ">
											<div class="row">
												<div class="col-3 col-sm-3">
													<div class="form-group m-b-40">
														<select id="PK_CAMPUS" name="PK_CAMPUS"  class="form-control" onchange="get_earning_detail(this.value)" >
															<option value=""></option>
															<? $res_type = $db->Execute("select CAMPUS_CODE,PK_CAMPUS from S_CAMPUS WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by CAMPUS_CODE ASC");
															while (!$res_type->EOF) { 
																$selected = ""; //DIAM-1815
																if($res_type->RecordCount() == 1){
																	$selected = 'selected'; 
																}
																//DIAM-1815 
																?>
																	
																<option value="<?=$res_type->fields['PK_CAMPUS'] ?>"  <?=$selected ?>><?=$res_type->fields['CAMPUS_CODE'] ?></option> <!-- //DIAM-1815 -->
															<?	$res_type->MoveNext();
															} ?>
														</select>
														
														<span class="bar"></span> 
														<label for="PK_CAMPUS"><?=CAMPUS?></label>
													</div>
												</div>
												<div class="col-9 col-sm-9 text-right">
													<button onclick="window.location.href='earnings_report'" type="button" class="btn waves-effect waves-light btn-info"><?=MNU_REPORTS ?></button>
												</div>
											</div>
											
											<div class="table-responsive p-20">
												<div id="earning_detail_div" >
												</div>
											</div>
										</div>	
									</div>
								</div>
							</form>
                        </div>
					</div>
				</div>
				
            </div>
        </div>

		<div class="modal" id="operationModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel1">
			<div class="modal-dialog" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<h4 class="modal-title" id="exampleModalLabel1"><?=CONFIRMATION?></h4>
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					</div>
					<div class="modal-body">
						<div class="form-group" id="operation_message" ></div>
						<input type="hidden" id="OPERATION_TYPE" value="0" />
						<input type="hidden" id="OPERATION_MONTH" value="0" />
						<input type="hidden" id="OPERATION_YEAR" value="0" />
					</div>
					<div class="modal-footer">
						<button type="button" onclick="conf_earning_operation(1)" class="btn waves-effect waves-light btn-info"><?=YES?></button>
						<button type="button" class="btn waves-effect waves-light btn-dark" onclick="conf_earning_operation(0)" ><?=NO?></button>
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
		//DIAM-1815 
		jQuery(document).ready(function() {
			if(jQuery("#PK_CAMPUS").val()!="")
				get_earning_detail(jQuery("#PK_CAMPUS").val()).delay( 800 );
		});
		//DIAM-1815 
		function get_earning_detail(val){
			jQuery(document).ready(function($) { 
				var data  = 'id='+val;
				var value = $.ajax({
					url: "ajax_get_earning_detail",	
					type: "POST",		 
					data: data,		
					async: false,
					cache: false,
					success: function (data) {	
						//alert(data)
						document.getElementById('earning_detail_div').innerHTML = data;
					}		
				}).responseText;
			});
		}
		
		function earning_operation(type, year, month, year_month){
			jQuery(document).ready(function($) {
				if(type == 'CALCULATE')
					document.getElementById('operation_message').innerHTML = 'Are you sure you want to Calcuate '+year_month+'?';
				else if(type == 'FINALIZE')
					document.getElementById('operation_message').innerHTML = 'Are you sure you want to Finalize '+year_month+'?';
				else if(type == 'DELETE')
					document.getElementById('operation_message').innerHTML = 'Are you sure you want to Delete '+year_month+'?';
				
				$("#operationModal").modal()
				$("#OPERATION_TYPE").val(type)
				$("#OPERATION_MONTH").val(month)
				$("#OPERATION_YEAR").val(year)
			});
		}
		
		function conf_earning_operation(val){
			jQuery(document).ready(function($) {
				if(val == 1) {
					var data  = 'type='+$("#OPERATION_TYPE").val()+'&month='+$("#OPERATION_MONTH").val()+'&year='+$("#OPERATION_YEAR").val()+'&campus='+$("#PK_CAMPUS").val();
					var value = $.ajax({
						url: "ajax_earning_operation",
						type: "POST",		 
						data: data,		
						async: false,
						cache: false,
						success: function (data) {	
							//alert(data)
							get_earning_detail($("#PK_CAMPUS").val())
						}		
					}).responseText;
				}
				$("#operationModal").modal("hide");
			});
		}
	</script>
</body>

</html>
