<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/menu.php");
require_once("../language/consolidation_tool.php");
require_once("check_access.php");

if(check_access('SETUP_CONSOLIDATION_TOOL') == 0){
	header("location:../index");
	exit;
}

$msg = "";
if(!empty($_POST)){
	//echo "<pre>";print_r($_POST);exit;
	//echo "CALL DSIS80001(".$_SESSION['PK_ACCOUNT'].", '".$_POST['CONSOLIDATE']."', ".$_POST['OLD_VALUE'].",".$_POST['NEW_VALUE'].",".$_SESSION['PK_USER'].")";exit;
	$res = $db->Execute("CALL DSIS80001(".$_SESSION['PK_ACCOUNT'].", '".$_POST['CONSOLIDATE']."', ".$_POST['OLD_VALUE'].",".$_POST['NEW_VALUE'].",".$_SESSION['PK_USER'].")");
	
	$db->close();
	$db->connect($db_host,'root',$db_pass,$db_name);
	
	foreach($_POST as $key => $val)
		unset($_POST[$key]);
	
	$msg = "Consolidated Successfully";
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
	<title><?=MNU_CONSOLIDATION_TOOL; ?> | <?=$title?></title>
	<link rel="stylesheet" type="text/css" href="../backend_assets/dist/css/easyui.css">
	<!-- Ticket # 718  -->	
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" integrity="sha512-nMNlpuaDPrqlEls3IX/Q56H36qvBASwb3ipuo3MxeWbsQB1881ox0cRv7UPTgBlriqoynt35KjEwgGUeUXIPnw==" crossorigin="anonymous" referrerpolicy="no-referrer" />	
	<style>
	
		.select2-container--default .select2-selection--single{
			border:0px;
			border-radius: 0px;
  		 	border-bottom: 1px solid #e9ecef;
			height: 40px;
		}
		.select2-container--default .select2-selection--single .select2-selection__arrow b{ display:none;}
		#CONSOLIDATE_DIV .select2-selection__arrow,
		#OLD_VALUE_DIV .select2-selection__arrow,
		#NEW_VALUE_DIV .select2-selection__arrow{
			text-indent: 1px;
			text-overflow: '';
			width: 100px;
			-webkit-appearance: none;
			-moz-appearance: none;
			appearance: none;
			padding: 2px 2px 2px 2px;
			border: none;
			background: transparent url("http://cdn1.iconfinder.com/data/icons/cc_mono_icon_set/blacks/16x16/br_down.png") no-repeat 60px center;
			width: 80px;
			overflow: hidden;
			appearance: none;
			}
		
	</style>
	<!-- Ticket # 718  -->
</head>

<body class="horizontal-nav boxed skin-megna fixed-layout">
   <? require_once("pre_load.php"); ?>
    <div id="main-wrapper">
       <? require_once("menu.php"); ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                 <div class="row page-titles">
                    <div class="col-md-12 align-self-center">
                        <h4 class="text-themecolor"><?=MNU_CONSOLIDATION_TOOL ?> </h4>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <form class="floating-labels m-t-40" method="post" name="form1" id="form1" enctype="multipart/form-data">
									<? if($msg != '') { ?>
									<div class="row d-flex" >	
										<div class="col-4 col-sm-4 form-group" style="color:red" >
											<?=$msg?>
										</div>
									</div>
									<? } ?>
									<div class="row d-flex" ><!-- //Ticket # 718 -->
										<div class="col-4 col-sm-4 form-group" id="CONSOLIDATE_DIV">		
											<select id="CONSOLIDATE" name="CONSOLIDATE" class="form-control required-entry" onchange="get_values(1)" >
												<option selected></option>
												<? $res_type = $db->Execute("select DISTINCT(CONSOLIDATE) as CONSOLIDATE from M_CONSOLIDATE WHERE ACTIVE = '1' ORDER BY CONSOLIDATE ASC ");
												while (!$res_type->EOF) { 
													$CONSOLIDATE = $res_type->fields['CONSOLIDATE'];  ?>
													<option value="<?=$CONSOLIDATE ?>" ><?=$CONSOLIDATE?></option>
												<?	$res_type->MoveNext();
												} ?>
											</select>
											<span class="bar"></span> 
											<label for="CONSOLIDATE"><?=SELECT_CONSOLIDATION_TOOL ?></label>
										</div>
										
										<div class="col-4 col-sm-4 form-group" id="OLD_VALUE_DIV" >
											<select id="OLD_VALUE" name="OLD_VALUE" class="form-control required-entry" onchange="get_values(2)" >
												<option selected></option>
											</select>
											<span class="bar"></span> 
											<label for="OLD_VALUE"><?=OLD_VALUE ?></label>
										</div>
										
										<div class="col-4 col-sm-4 form-group" id="NEW_VALUE_DIV" >
											<select id="NEW_VALUE" name="NEW_VALUE" class="form-control required-entry" >
												<option selected></option>
											</select>
											<span class="bar"></span> 
											<label for="NEW_VALUE"><?=NEW_VALUE ?></label>
										</div>
										
									</div>
														
									<br />
									<div class="row">
                                        <div class="col-md-6">
											<div class="form-group m-b-5"  style="text-align:right" >
												<input type="hidden" id="WARNING" value="<?=WARNING?>" />
												
												<input type="hidden" id="count1" value="0" >
												<button type="button" onclick="show_warning()"  name="btn" class="btn waves-effect waves-light btn-info"><?=CONTINUE1?></button>
												<button type="button" class="btn waves-effect waves-light btn-dark" onclick="window.location.href='setup'" ><?=CANCEL?></button>
												
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
		
		<div class="modal" id="confirmationModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel1">
			<div class="modal-dialog" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<h4 class="modal-title" id="exampleModalLabel1"><?=CONFIRMATION?></h4>
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					</div>
					<div class="modal-body">
						<div class="form-group" id="WARNING_DIV" ></div>
					</div>
					<div class="modal-footer">
						Consolidate <input type="text" id="count_2" style="width:100px" > Records &nbsp;&nbsp;&nbsp;
						<button type="button" onclick="conf_form_submit(1)" class="btn waves-effect waves-light btn-info"><?=CONSOLIDATE?></button>
						<button type="button" class="btn waves-effect waves-light btn-dark" onclick="conf_form_submit(0)" ><?=CANCEL?></button>
					</div>
				</div>
			</div>
		</div>
    </div>
   
	<? require_once("js.php"); ?>
	<script src="../backend_assets/dist/js/validation_prototype.js"></script>
	<script src="../backend_assets/dist/js/validation.js"></script>
	<script type="text/javascript">
		var form1 = new Validation('form1');
		
		function get_values(type){
			jQuery(document).ready(function($) {
				var data = "CONSOLIDATE="+document.getElementById('CONSOLIDATE').value+'&type='+type;
				if(type == 2)
					data += '&OLD_VALUE='+document.getElementById('OLD_VALUE').value;
				$.ajax({
					type: "POST",
					url:"ajax_get_consolidate_value",
					data:data,
					success: function(result1){ 
						result1 = result1.split("|||");
						if(type == 1){
							document.getElementById('OLD_VALUE_DIV').innerHTML  = result1[0]
							document.getElementById('count1').value 			= 0
							$('#NEW_VALUE').empty();
							$("#OLD_VALUE").select2(); //Ticket # 718
						} else if(type == 2){
							document.getElementById('NEW_VALUE_DIV').innerHTML 	= result1[0]
							document.getElementById('count1').value 			= result1[1]
							document.getElementById('NEW_VALUE').setAttribute("onchange", "get_valuesfocus()");//Ticket # 718
							$("#NEW_VALUE").select2(); //Ticket # 718
						}
						
						$('.floating-labels .form-control').on('focus blur', function (e) {
							$(this).parents('.form-group').toggleClass('focused', (e.type === 'focus' || this.value.length > 0));
						}).trigger('blur');
					}
				});
			});	
		}
		
		function show_warning(){
			jQuery(document).ready(function($) {
				var valid = new Validation('form1', {onSubmit:false});
				var result = valid.validate();
				if(result == true){
					var count1  = document.getElementById('count1').value
					var WARNING = document.getElementById('WARNING').value
					
					
					WARNING = WARNING.replace("{number of records}", count1);
					document.getElementById('WARNING_DIV').innerHTML = WARNING
					
					$("#confirmationModal").modal()
				}
			});	
		}
		
		function conf_form_submit(val){
			jQuery(document).ready(function($) {
				if(val == 1) {
					var count1  = document.getElementById('count1').value
					var count2  = $("#count_2").val()
					
					if(count1 == count2) {
						var valid = new Validation('form1', {onSubmit:false});
						var result = valid.validate();
						if(result == true){
							document.form1.submit()
						}
					} else {
						alert("Count Does Not Match");
					}
				} else 
					$("#confirmationModal").modal("hide");
			});	
		}
	</script>
	
	<script type="text/javascript" src="https://code.jquery.com/jquery-migrate-1.4.1.min.js"></script>
	<script src="../backend_assets/dist/js/validation_prototype.js"></script>
	<script src="../backend_assets/dist/js/validation.js"></script>
	<!-- Ticket # 718  -->	
	<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js" integrity="sha512-2ImtlRlf2VVmiGZsjm9bEyhjGW4dU7B6TNwh/hx/iSByxNENtj3WVE6o/9Lj4TJeVXPi4bnOIMXFIJJAeufa0A==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
		<script>
			jQuery(document).ready(function($){
				// Initialize select2
				$("#OLD_VALUE").select2();
				$("#NEW_VALUE").select2();
				$('#CONSOLIDATE').select2({
					minimumResultsForSearch: Infinity
				});		
			});

			
		
		function get_valuesfocus(){
			jQuery(document).ready(function($) {
			$('.floating-labels .form-control').on('focus blur', function (e) {
							$(this).parents('.form-group').toggleClass('focused', (e.type === 'focus' || this.value.length > 0));
						}).trigger('blur');
					});
		}
	</script>
   <!-- Ticket # 718  -->
</body>

</html>
