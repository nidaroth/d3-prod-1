<? require_once("../global/config.php"); 
if($_SESSION['ADMIN_PK_USER'] == 0 || $_SESSION['ADMIN_PK_USER'] == '' || $_SESSION['ADMIN_PK_ROLES'] != 1 ){ 
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
	<title>Employee | <?=$title?></title>
</head>

<body class="horizontal-nav boxed skin-megna fixed-layout">
   <? require_once("pre_load.php"); ?>
    <div id="main-wrapper">
       <? require_once("menu.php"); ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                 <div class="row page-titles">
                    <div class="col-md-5 align-self-center">
                        <h4 class="text-themecolor">Employee</h4>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="card">
							<ul class="nav nav-tabs customtab" role="tablist">
                                <li class="nav-item"> <a class="nav-link active" data-toggle="tab" href="#generalTab" role="tab"><span class="hidden-sm-up"><i class="ti-home"></i></span> <span class="hidden-xs-down">Employee</span></a> </li>
                            </ul>
                            <!-- Tab panes -->
                            <div class="tab-content">
                                <div class="tab-pane active" id="generalTab" role="tabpanel">
                                    <div class="p-20">
                                        <form class="floating-labels" autocomplete="off">
											<div class="d-flex">
		                                    	<div class="col-12 col-sm-3 form-group">
		                                    		<input id="PREFIX" name="PREFIX" type="text" class="form-control" value="">
			                                        <span class="bar"></span> 
			                                        <label for="PREFIX">Prefix</label>
		                                    	</div>
		                                    	<div class="col-12 col-sm-3 form-group">
		                                    		<input id="LAST_NAME" name="LAST_NAME" type="text" class="form-control" value="">
			                                        <span class="bar"></span> 
			                                        <label for="LAST_NAME">Last Name</label>
		                                    	</div>
		                                    	<div class="col-12 col-sm-3 form-group">
		                                    		<input id="FIRST_NAME" name="FIRST_NAME" type="text" class="form-control" value="">
			                                        <span class="bar"></span> 
			                                        <label for="FIRST_NAME">First Name</label>
		                                    	</div>
		                                    	<div class="col-12 col-sm-3 form-group">
		                                    		<input id="MIDDLE_NAME" name="MIDDLE_NAME" type="text" class="form-control" value="">
			                                        <span class="bar"></span> 
			                                        <label for="MIDDLE_NAME">Middle Name</label>
		                                    	</div>
		                                    </div>
											
		                                    <div class="d-flex">
		                                    	<div class="col-12 col-sm-3 form-group">
		                                    		<input id="SSN" name="SSN" type="text" class="form-control" value="">
			                                        <span class="bar"></span> 
			                                        <label for="SSN">SSN</label>
		                                    	</div>
		                                    	<div class="col-12 col-sm-3 form-group">
		                                    		<input id="EMPLOYEE_ID" name="EMPLOYEE_ID" type="text" class="form-control" value="">
			                                        <span class="bar"></span> 
			                                        <label for="EMPLOYEE_ID">Employee ID</label>
		                                    	</div>
		                                        <div class="col-12 col-sm-3 form-group">
		                                        	<input id="DSIS_USER" name="DSIS_USER" type="text" class="form-control" value="">
			                                        <span class="bar"></span> 
			                                        <label for="DSIS_USER">DSIS User</label>
		                                        </div>
		                                    </div>
		                                    
											<div class="row">
		                                    	<div class="col-sm-6">
													<div class="d-flex">
														<div class="col-12 col-sm-12 form-group">
															<input id="ADDRESS" name="ADDRESS" type="text" class="form-control" value="">
															<span class="bar"></span>
															<label for="ADDRESS">Address</label>
														</div>
													</div>
													<div class="d-flex">
														<div class="col-12 col-sm-12 form-group">
															<input id="ADDRESS1" name="ADDRESS1" type="text" class="form-control" value="">
															<span class="bar"></span>
															<label for="ADDRESS1">Address 2</label>
														</div>
													</div>
													
													<div class="d-flex">
														<div class="col-12 col-sm-6 form-group">
															<input id="CITY" name="CITY" type="text" class="form-control" value="">
															<span class="bar"></span> 
															 <label for="CITY">City</label>
														</div>
														<div class="col-12 col-sm-6 form-group">
															<select id="PK_STATES" name="PK_STATES" class="form-control" onchange="get_country(this.value,'PK_COUNTRY')" >
																<option selected></option>
																 <? $res_type = $db->Execute("select PK_STATES, STATE_NAME from Z_STATES WHERE ACTIVE = '1' ORDER BY STATE_NAME ASC ");
																while (!$res_type->EOF) { ?>
																	<option value="<?=$res_type->fields['PK_STATES'] ?>" ><?=$res_type->fields['STATE_NAME']?></option>
																<?	$res_type->MoveNext();
																} ?>
															</select>
															<span class="bar"></span> 
															<label for="STATE">State</label>
														</div>
													</div>
													
													<div class="d-flex">
														<div class="col-12 col-sm-6 form-group">
															<input id="ZIP" name="ZIP" type="text" class="form-control" value="">
															<span class="bar"></span> 
															 <label for="ZIP">Zip</label>
														</div>	
														<div class="col-12 col-sm-6 form-group" id="PK_COUNTRY_LABEL" >
															<div id="PK_COUNTRY_DIV" >
																<select id="PK_COUNTRY" name="PK_COUNTRY" class="form-control">
																	<option selected></option>
																</select>
															</div>
															<span class="bar"></span> 
															<label for="COUNTRY">Country</label>
														</div>	                                        
													</div>
												</div>
												<div class="col-sm-6">
													<div class="d-flex">
														<div class="col-12 col-sm-6 form-group">
															<input id="HOME_PHONE" name="HOME_PHONE" type="text" class="form-control" value="">
															<span class="bar"></span> 
															<label for="HOME_PHONE">Home Phone</label>
														</div>
														<div class="col-12 col-sm-6 form-group">
															<input id="WORK_PHONE" name="WORK_PHONE" type="text" class="form-control" value="">
															<span class="bar"></span> 
															<label for="WORK_PHONE">Work Phone</label>
														</div>
													</div>
													<div class=" d-flex">
														<div class="col-12 col-sm-6 form-group">
															<input id="CELL_PHONE" name="CELL_PHONE" type="text" class="form-control" value="">
															<span class="bar"></span> 
															<label for="CELL_PHONE">Cell Phone</label>
														</div>
														
														<div class="col-12 col-sm-6 form-group">
															<input id="EMAIL" name="EMAIL" type="text" class="form-control" value="">
															<span class="bar"></span> 
															<label for="EMAIL">Email</label>
														</div>
													</div>
													
													<div class="d-flex">
														<div class="form-group col-12 col-sm-6">
															<input class="form-control" type="date" value="2011-08-19" name="DATE_OF_BIRTH" id="DATE_OF_BIRTH">
															<span class="bar"></span> 
															<label for="DATE_OF_BIRTH">Date Of Birth</label>
														</div>
														
														<div class="col-12 col-sm-6 form-group">
															<select id="GENDER" name="GENDER" class="form-control">
																<option selected></option>
															</select>
															<span class="bar"></span> 
															 <label for="GENDER">Gender</label>
														</div>
													</div>
													
													<div class="d-flex">
														<div class="col-12 col-sm-6 form-group">
															<select id="MARITAL_STATUS" name="MARITAL_STATUS" class="form-control">
																<option selected></option>
																<? $res_type = $db->Execute("select * from Z_MARITAL_STATUS order by MARITAL_STATUS ASC");
																while (!$res_type->EOF) { ?>
																	<option value="<?=$res_type->fields['PK_MARITAL_STATUS']?>"><?=$res_type->fields['MARITAL_STATUS']?></option>
																<?	$res_type->MoveNext();
																} ?>
															</select>
															<span class="bar"></span> 
															 <label for="MARITAL_STATUS">Marital Status</label>
														</div>
														<div class="col-12 col-sm-6 form-group">
															<input id="IPEDS" name="IPEDS" type="text" class="form-control" value="">
															<span class="bar"></span> 
															 <label for="IPEDS">IPEDS Ethnicity</label>
														</div>
													</div>
													
												</div>
		                                    </div>
											
	                                        <div class="d-flex">
		                                        <div class="col-12 col-sm-3 form-group">
		                                    		<input id="NETWORK_ID" name="NETWORK_ID" type="text" class="form-control" value="">
			                                        <span class="bar"></span> 
			                                         <label for="NETWORK_ID">Network ID</label>
		                                    	</div>
		                                    	<div class="col-12 col-sm-3 form-group">
		                                    		<input id="COMPANY_EMPLOYEE_ID" name="COMPANY_EMPLOYEE_ID" type="text" class="form-control" value="">
			                                        <span class="bar"></span> 
			                                         <label for="COMPANY_EMPLOYEE_ID">Company Emp ID</label>
		                                    	</div>
	                                      
		                                    	<div class="col-12 col-sm-3 form-group">
		                                    		<input class="form-control" type="date" value="2011-08-19" name="DATE_HIRED" id="DATE_HIRED">
			                                        <span class="bar"></span> 
			                                        <label for="DATE_HIRED">Date Hired</label>
		                                    	</div>
		                                    	<div class="col-12 col-sm-3 form-group">
		                                    		<input class="form-control" type="date" value="2011-08-19" name="DATE_TERMINATED" id="DATE_TERMINATED">
			                                        <span class="bar"></span> 
			                                        <label for="DATE_TERMINATED">Date Terminated</label>
		                                    	</div>
	                                    	</div>
		                                    <div class="d-flex">
		                                    	<div class="col-12 col-sm-3 form-group">
		                                    		<input id="SUPERVISOR" name="SUPERVISOR" type="text" class="form-control" value="">
			                                        <span class="bar"></span> 
			                                        <label for="SUPERVISOR">Supervisor</label>
		                                    	</div>
		                                    	<div class="col-12 col-sm-3 form-group">
		                                    		<input id="TITLE" name="TITLE" type="text" class="form-control" value="">
			                                        <span class="bar"></span> 
			                                        <label for="TITLE">Title</label>
		                                    	</div>
		                                    	<div class="form-group col-12 col-sm-3">
		                                    		<select id="DEPARTMENT" name="DEPARTMENT" class="form-control">
		                                    			<option></option>
		                                    			<? $res_type = $db->Execute("select * from M_DEPARTMENT_MASTER order by DEPARTMENT ASC");
														while (!$res_type->EOF) { ?>
															<option value="<?=$res_type->fields['PK_DEPARTMENT_MASTER']?>"><?=$res_type->fields['DEPARTMENT']?></option>
														<?	$res_type->MoveNext();
														} ?>
	                                    			</select>
			                                        <span class="bar"></span> 
			                                        <label for="DEPARTMENT">Department</label>
		                                    	</div>
		                                    </div>
		                                    <div class="d-flex">
		                                    	<div class="form-group col-12 col-sm-3">
		                                    		<select id="FULL_PART_TIME" name="FULL_PART_TIME" class="form-control">
		                                    			<option></option>
	                                    			</select>
			                                        <span class="bar"></span> 
			                                        <label for="FULL_PART_TIME">Full/Part Time</label>
		                                    	</div>
		                                    	<div class="form-group col-12 col-sm-3">
		                                    		<select id="CAMPUS" name="CAMPUS" class="form-control">
		                                    			<option></option>
	                                    			</select>
			                                        <span class="bar"></span> 
			                                        <label for="CAMPUS">Campus</label>
		                                    	</div>
		                                    	<div class="form-group col-12 col-sm-3">
		                                    		<select id="ACTIVE" name="ACTIVE" class="form-control">
		                                    			<option></option>
	                                    			</select>
			                                        <span class="bar"></span> 
			                                        <label for="ACTIVE">Active</label>
		                                    	</div>
	                                    	</div>
	                                    	<div class="d-flex">
		                                    	<div class="form-group col-12 col-sm-3">
		                                    		<select id="ELIGIBLE_FOR_REHIRE" name="ELIGIBLE_FOR_REHIRE" class="form-control">
		                                    			<option></option>
	                                    			</select>
			                                        <span class="bar"></span> 
			                                        <label for="ELIGIBLE_FOR_REHIRE">Eligible for Rehire</label>
		                                    	</div>
		                                    	<div class="form-group col-12 col-sm-3">
		                                    		<select id="SOC_CODE" name="SOC_CODE" class="form-control">
		                                    			<option></option>
	                                    			</select>
			                                        <span class="bar"></span> 
			                                        <label for="SOC_CODE">SOC Code</label>
		                                    	</div>
		                                    </div>
		                                    <div class="form-group">
		                                        <button type="submit" class="btn waves-effect waves-light btn-info">Submit</button>
		                                        <button type="submit" class="btn waves-effect waves-light btn-dark">Cancel</button>
		                                    </div>
		                                </form>
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
	<script src="../backend_assets/dist/js/validation_prototype.js"></script>
	<script src="../backend_assets/dist/js/validation.js"></script>
	<script type="text/javascript">
		var form1 = new Validation('form1');
		
		function get_country(val,id){
			jQuery(document).ready(function($) { 
				var data  = 'state='+val+'&id='+id;
				var value = $.ajax({
					url: "ajax_get_country_from_state",	
					type: "POST",		 
					data: data,		
					async: false,
					cache: false,
					success: function (data) {	
						//alert(data)
						document.getElementById(id).innerHTML = data;
						document.getElementById('PK_COUNTRY_LABEL').classList.add("focused");
						
					}		
				}).responseText;
			});
		}
	</script>

</body>

</html>