<? require_once("../global/config.php"); 

//echo date_default_timezone_get();exit;
//echo "<pre>";print_r($_SESSION);exit;
if($_SESSION['PK_USER'] == 0 || $_SESSION['PK_USER'] == '' ){ 
	header("location:../index");
	exit;
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
	<title>Dashboard | <?=$title?></title>
</head>

<body class="horizontal-nav boxed skin-megna fixed-layout">
   <? require_once("pre_load.php"); ?>
    <div id="main-wrapper">
       <? require_once("menu.php"); ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                <div class="row page-titles">
                    <div class="col-md-5 align-self-center">
                        <h4 class="text-themecolor">DASHBOARD</h4>
                    </div>
                </div>

				
				<div class="card-group">
					<div class="card">
						<div class="card-body">
							<div class="row">
								<div class="col-12">
									<div class="row dsis-new">	
										<div class="col-md-4">
											<div class="row">
												<div class="col-md-12 text-center">
													<h4>Academic Report Year</h4>
													<div class="m-10 font-bold">2020-21</div>	
												</div>
												<div class="col-md-8">
													<div class="row">
														<div class="col-md-12 text-center">
															<h4>Total Credit Students</h4>		
														</div>
														<div class="col-md-12 text-center m-b-10">
															<h5 class="m-10 font-bold">5,143</h5>		
														</div>
														<div class="col-md-12 text-center">
															<h4>Veterans</h4>		
														</div>
														<div class="col-md-12 text-center m-b-10">
															<h5 class="m-10 font-bold">343</h5>		
														</div>
													</div>
													
												</div>
												<div class="col-md-4">
													<div class="row">
														<div class="col-md-12">
															<h4>Pell Recipients</h4>
														</div>
														<div class="col-md-12">
															<img class="w-100" src="../backend_assets/images/dsis-2.jpg">
														</div>
													</div>
												</div>
												<div class="col-md-12 text-center">
													<h4>Age Group</h4>
													<img class="m-10 w-100" src="../backend_assets/images/dsis-3.jpg">
												</div>
												<div class="col-md-12 text-center">
													<h4>First Generation Students</h4>
													<img class="w-100" src="../backend_assets/images/dsis-4.jpg">
												</div>
											</div>
											
											
										</div>
										<div class="col-md-6">
											<div class="row">
												<div class="col-md-12 text-center">
													<h2 class="m-0 ">Credit Students</h2>
													<h6 class="m-b-10">Demographic Data</h6>
												</div>
												<div class="col-md-12">
													<h4 class="m-10">Race/Ethnicity</h4>
												</div>
												<div class="col-md-12">
													<img class="w-100" src="../backend_assets/images/dsis-1.jpg">
												</div>
												<div class="col-md-12">
													<h4 class="m-10">Campus Attendance*</h4>
												</div>
												<div class="col-md-12">
													<img class="w-100" src="../backend_assets/images/dsis-5.jpg">
												</div>
											</div>
										</div>
										<div class="col-md-2">
											<div class="row radio-custom">
												<div class="col-md-12">
													<h4 class="m-b-10">Academic Year</h4>
												</div>
												<div class="col-md-12 m-b-10 radio-options">
													<div class="">
														<label>
														  <input type="radio" name="radio">
														  <span>2021</span>
														</label>
													</div>
													<div class="">
														<label>
														  <input type="radio" name="radio">
														  <span>2022</span>
														</label>
													</div>
													<div class="">
														<label>
														  <input type="radio" name="radio">
														  <span>2023</span>
														</label>
													</div>
													<div class="">
														<label>
														  <input type="radio" name="radio">
														  <span>2024</span>
														</label>
													</div>
												</div>
											</div>
											<div class="row">
												<div class="col-md-12">
													<h4 class="m-b-10">County of Residence</h4>
												</div>
												<div class="col-md-12">
													<div class="residence">
														<div class="residence-name">jackson</div>
														<span class="residence-number">3,313</span>
														<span class="residence-total">total</span>
													</div>
													<div class="residence">
														<div class="residence-name">josephine</div>
														<span class="residence-number">1394</span>
														<span class="residence-total">total</span>
													</div>
													<div class="residence">
														<div class="residence-name">other location</div>
														<span class="residence-number">436</span>
														<span class="residence-total">total</span>
													</div>
												</div>
											</div>
											<div class="row">
												<div class="col-md-12">
													<h4 class="">Gender</h4>
												</div>
												<div class="col-md-12">
													<img class="w-100" src="../backend_assets/images/dsis-6.jpg">
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
						<h4 class="modal-title" id="exampleModalLabel1">Delete</h4>
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					</div>
					<div class="modal-body">
						<div class="form-group">
							Are you sure, you want to Delete this record?
							<input type="hidden" id="DELETE_ID" value="0" />
						</div>
					</div>
					<div class="modal-footer">
						<button type="button" onclick="conf_delete(1)" class="btn waves-effect waves-light btn-info">Yes</button>
						<button type="button" class="btn waves-effect waves-light btn-dark" onclick="conf_delete(0)" >No</button>
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
		
		function delete_row(id){
			jQuery(document).ready(function($) {
				$("#deleteModal").modal()
				$("#DELETE_ID").val(id)
			});
		}
		function conf_delete(val,id){
			jQuery(document).ready(function($) {
				if(val == 1)
					window.location.href = 'index?act=del&id='+$("#DELETE_ID").val();
				else
					$("#deleteModal").modal("hide");
			});
		}
		
		function show_div(id){
			if(document.getElementById(id).style.display == 'block')
				document.getElementById(id).style.display = 'none';
			else	
				document.getElementById(id).style.display = 'block'
		}
	</script>
	
</body>

</html>