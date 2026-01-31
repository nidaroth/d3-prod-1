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
	<title>Student | <?=$title?></title>
</head>

<body class="horizontal-nav boxed skin-megna fixed-layout">
   <? require_once("pre_load.php"); ?>
    <div id="main-wrapper">
       <? require_once("menu.php"); ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                 <div class="row page-titles">
                    <div class="col-md-5 align-self-center">
                        <h4 class="text-themecolor">Student</h4>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="card">
							<ul class="nav nav-tabs customtab" role="tablist">
                                <li class="nav-item"> <a class="nav-link active" data-toggle="tab" href="#generalTab" role="tab"><span class="hidden-sm-up"><i class="ti-home"></i></span> <span class="hidden-xs-down">Lead</span></a> </li>
                                <li class="nav-item"> <a class="nav-link" data-toggle="tab" href="#campusTab" role="tab"><span class="hidden-sm-up"><i class="ti-user"></i></span> <span class="hidden-xs-down">Other</span></a> </li>
                            </ul>
                            <!-- Tab panes -->
                            <div class="tab-content">
                                <div class="tab-pane active" id="generalTab" role="tabpanel">
                                    <div class="p-20">
                                        <form class="floating-labels">
		                                    <div class="d-flex">
		                                    	<div class="col-12 col-sm-3 form-group">
													<input id="FIRST_NAME" name="FIRST_NAME" type="text" class="form-control" value="bbb">
													<span class="bar"></span> 
													<label for="FIRST_NAME">First Name</label>
		                                    	</div>
		                                    	<div class="col-12 col-sm-3 form-group">
		                                    		<input id="LAST_NAME" name="LAST_NAME" type="text" class="form-control" value="">
			                                        <span class="bar"></span> 
			                                        <label for="LAST_NAME">Last Name</label>
		                                    	</div>
		                                        <div class="col-12 col-sm-3 form-group">
		                                        	<input id="MIDDLE_NAME" name="MIDDLE_NAME" type="text" class="form-control" value="">
			                                        <span class="bar"></span> 
			                                        <label for="MIDDLE_NAME">Middle Name</label>
		                                        </div>
		                                    </div>
		                                    <div class="d-flex">
		                                    	<div class="col-12 col-sm-3 form-group">
		                                    		<input id="OTHER_NAME" name="OTHER_NAME" type="text" class="form-control" value="">
			                                        <span class="bar"></span> 
			                                        <label for="OTHER_NAME">Other Name</label>
		                                    	</div>
		                                    	<div class="col-12 col-sm-3 form-group">
		                                    		<input id="SSN" name="SSN" type="text" class="form-control" value="">
			                                        <span class="bar"></span> 
			                                        <label for="SSN">SSN</label>
		                                    	</div>
		                                    </div>
		                                    
											<div class="row">
		                                    	<div class="col-sm-6">
													<div class="d-flex">
														<div class="col-12 col-sm-12 form-group">
															<input id="CAMPUS_ADDRESS" name="CAMPUS_ADDRESS" type="text" class="form-control" value="">
															<span class="bar"></span>
															<label for="CAMPUS_ADDRESS">Address</label>
														</div>
													</div>
													<div class="d-flex">
														<div class="col-12 col-sm-12 form-group">
															<input id="CAMPUS_ADDRESS1" name="CAMPUS_ADDRESS1" type="text" class="form-control" value="">
															<span class="bar"></span>
															<label for="CAMPUS_ADDRESS1">Address 2</label>
														</div>
													</div>
													
													<div class="d-flex">
														<div class="col-12 col-sm-6 form-group">
															<input id="CAMPUS_CITY" name="CAMPUS_CITY" type="text" class="form-control" value="">
															<span class="bar"></span> 
															 <label for="CAMPUS_CITY">City</label>
														</div>
														<div class="col-12 col-sm-6 form-group">
															<select id="CAMPUS_PK_STATES" name="CAMPUS_PK_STATES" class="form-control" onchange="get_country(this.value,'CAMPUS_PK_COUNTRY')" >
																<option selected></option>
																 <? $res_type = $db->Execute("select PK_STATES, STATE_NAME from Z_STATES WHERE ACTIVE = '1' ORDER BY STATE_NAME ASC ");
																while (!$res_type->EOF) { ?>
																	<option value="<?=$res_type->fields['PK_STATES'] ?>" ><?=$res_type->fields['STATE_NAME']?></option>
																<?	$res_type->MoveNext();
																} ?>
															</select>
															<span class="bar"></span> 
															<label for="CAMPUS_STATE">State</label>
														</div>
													</div>
													
													<div class="d-flex">
														<div class="col-12 col-sm-6 form-group">
															<input id="CAMPUS_ZIP" name="CAMPUS_ZIP" type="text" class="form-control" value="">
															<span class="bar"></span> 
															 <label for="CAMPUS_ZIP">Zip</label>
														</div>	
														<div class="col-12 col-sm-6 form-group" id="CAMPUS_PK_COUNTRY_LABEL" >
															<div id="CAMPUS_PK_COUNTRY_DIV" >
																<select id="CAMPUS_PK_COUNTRY" name="CAMPUS_PK_COUNTRY" class="form-control">
																	<option selected></option>
																</select>
															</div>
															<span class="bar"></span> 
															<label for="CAMPUS_COUNTRY">Country</label>
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
													<div class="d-flex">
														<div class="col-12 col-sm-6 form-group">
															<input id="MOBILE_PHONE" name="MOBILE_PHONE" type="text" class="form-control" value="">
															<span class="bar"></span> 
															 <label for="MOBILE_PHONE">Mobile Phone</label>
														</div>
														<div class="col-12 col-sm-6 custom-control custom-checkbox form-group">
															<input type="checkbox" class="custom-control-input" id="OPTOUT" value="check">
															<label class="custom-control-label" for="OPTOUT">OPT OUT</label>
														</div>
													</div>
													
													<div class="d-flex">
														<div class="col-12 col-sm-6 form-group">
															<input id="EMERGENCY" name="EMERGENCY" type="text" class="form-control" value="">
															<span class="bar"></span> 
															 <label for="EMERGENCY">Emergency</label>
														</div>
														<div class="col-12 col-sm-6 form-group">
															<input id="FAX" name="FAX" type="text" class="form-control" value="">
															<span class="bar"></span> 
															 <label for="FAX">Fax</label>
														</div>
													</div>
													
													<div class="d-flex">
														<div class="col-12 col-sm-6 form-group">
															<input id="EMAIL" name="EMAIL" type="text" class="form-control" value="">
															<span class="bar"></span> 
															 <label for="EMAIL">Email</label>
														</div>
														
														<div class="col-12 col-sm-4 form-group">
															<input id="EMAIL_OTHER" name="EMAIL_OTHER" type="text" class="form-control" value="">
															<span class="bar"></span> 
															 <label for="EMAIL_OTHER">Email Other</label>
														</div>
														
														<div class="custom-control custom-checkbox col-12 col-sm-2">
															<input type="checkbox" class="custom-control-input" id="USE_EMAIL" value="check">
															<label class="custom-control-label" for="USE_EMAIL">Use Email</label>
														</div>
													</div>
												</div>
											</div>
											
		                                    <div class="d-flex">
		                                    	<div class="col-12 col-sm-3 form-group">
		                                    		<select id="REPRESENTATIVE" name="REPRESENTATIVE" class="form-control">
		                                    			<option></option>
		                                    		</select>
			                                        <span class="bar"></span> 
			                                        <label for="REPRESENTATIVE">Representative</label>
		                                    	</div>
		                                    	<div class="col-12 col-sm-3 form-group">
		                                    		<select id="SECOND_REP" name="SECOND_REP" class="form-control">
		                                    			<option></option>
		                                    		</select>
			                                        <span class="bar"></span> 
			                                        <label for="SECOND_REP">Second Rep</label>
		                                    	</div>
		                                    	<div class="col-12 col-sm-3 form-group">
		                                    		<select id="SOURCE_CODE" name="SOURCE_CODE" class="form-control">
		                                    			<option></option>
		                                    		</select>
			                                        <span class="bar"></span> 
			                                        <label for="SOURCE_CODE">Source Code</label>
		                                    	</div>
	                                    	</div>
	                                    	<div class="d-flex">
		                                    	<div class="col-12 col-sm-3 form-group">
		                                    		<select id="CONTACT_SOURCE" name="CONTACT_SOURCE" class="form-control">
		                                    			<option></option>
		                                    		</select>
			                                        <span class="bar"></span> 
			                                        <label for="CONTACT_SOURCE">Contact Source</label>
		                                    	</div>
		                                    	<div class="col-12 col-sm-3 form-group">
		                                    		<select id="CUSTOM1" name="CUSTOM1" class="form-control">
		                                    			<option></option>
		                                    		</select>
			                                        <span class="bar"></span> 
			                                        <label for="CUSTOM1">Custom 1</label>
		                                    	</div>
		                                    </div>
		                                    <div class="d-flex">
		                                    	<div class="col-12 col-sm-3 form-group">
		                                    		<select id="STATUS_CODE" name="STATUS_CODE" class="form-control">
		                                    			<option></option>
		                                    		</select>
			                                        <span class="bar"></span> 
			                                        <label for="STATUS_CODE">Status Code</label>
		                                    	</div>
		                                    	<div class="col-12 col-sm-3 form-group">
		                                    		<input class="form-control" type="date" value="2011-08-19" id="STATUS_DATE" name="STATUS_DATE">
			                                        <span class="bar"></span> 
			                                        <label for="STATUS_DATE">Status Date</label>
		                                    	</div>
		                                    	<div class="col-12 col-sm-3 form-group">
		                                    		<select id="PROGRAM" name="PROGRAM" class="form-control">
		                                    			<option></option>
		                                    		</select>
			                                        <span class="bar"></span> 
			                                        <label for="PROGRAM">Program</label>
		                                    	</div>
	                                    	</div>
	                                    	<div class="d-flex">
	                                    		<div class="col-12 col-sm-3 form-group">
		                                    		<input class="form-control" type="date" value="2011-08-19" id="FIRST_TERM_DATE" name="FIRST_TERM_DATE">
			                                        <span class="bar"></span> 
			                                        <label for="FIRST_TERM_DATE">First Term Date</label>
		                                    	</div>
		                                    	<div class="col-12 col-sm-3 form-group">
		                                    		<select id="FUNDING" name="FUNDING" class="form-control">
		                                    			<option></option>
		                                    			<? $res_type = $db->Execute("select * from M_FUNDING_MASTER order by FUNDING ASC");
														while (!$res_type->EOF) { ?>
															<option value="<?=$res_type->fields['PK_FUNDING_MASTER']?>"><?=$res_type->fields['FUNDING']?></option>
														<?	$res_type->MoveNext();
														} ?>
		                                    		</select>
			                                        <span class="bar"></span> 
			                                        <label for="FUNDING">Funding</label>
		                                    	</div>
		                                    </div>
		                                    <div class="d-flex">
		                                    	<div class="col-12 form-group">
		                                    		<textarea class="form-control" rows="2" id="NOTES" name="NOTES"></textarea>
			                                        <span class="bar"></span>
			                                        <label for="NOTES">Notes</label>
		                                    	</div>
		                                    </div>
		                                    <div class="d-flex">
		                                    	<div class="col-12 col-sm-3 form-group">
		                                    		<input class="form-control" type="date" value="2011-08-19" id="ENTRY_DATE" name="ENTRY_DATE">
			                                        <span class="bar"></span> 
			                                        <label for="ENTRY_DATE">Entry Date</label>
		                                    	</div>
		                                    	<div class="col-12 col-sm-3 form-group">
			                                        <input id="ENTRY_TIME" name="ENTRY_TIME" type="text" class="form-control" value="">
			                                        <span class="bar"></span> 
			                                         <label for="ENTRY_TIME">Entry Time</label>
			                                    </div>
		                                   
		                                    	<div class="col-12 col-sm-3 form-group">
		                                    		<input id="LEAD_ID" name="LEAD_ID" type="text" class="form-control" value="">
			                                        <span class="bar"></span> 
			                                         <label for="LEAD_ID">Lead ID</label>
		                                    	</div>
		                                    	<div class="col-12 col-sm-3 form-group">
		                                    		<input id="ADM_USER_ID" name="ADM_USER_ID" type="text" class="form-control" value="">
			                                        <span class="bar"></span> 
			                                         <label for="ADM_USER_ID">ADM User ID</label>
		                                    	</div>
		                                    </div>
		                                    <div class="form-group">
		                                        <button type="submit" class="btn waves-effect waves-light btn-info">Submit</button>
		                                        <button type="submit" class="btn waves-effect waves-light btn-dark">Cancel</button>
		                                    </div>
		                                </form>
                                    </div>
                                </div>
                                <div class="tab-pane" id="campusTab" role="tabpanel">
                                	<div class="p-20">
                                        <form class="floating-labels">
                                        	<div class="d-flex">
		                                    	<div class="col-12 col-sm-4 form-group">
		                                    		<input id="HS_CLASS_RANK" name="HS_CLASS_RANK" type="text" class="form-control" value="">
			                                        <span class="bar"></span> 
			                                        <label for="HS_CLASS_RANK">HS Class Rank</label>
		                                    	</div>
		                                    	<div class="col-12 col-sm-4 form-group>
		                                    		<input id="HS_CGPA" name="HS_CGPA" type="text" class="form-control" value="">
			                                        <span class="bar"></span> 
			                                        <label for="HS_CGPA">HS CGPA</label>
		                                    	</div>
		                                    	<div class="col-12 col-sm-4 form-group">
		                                    		<input id="POST_SEC_CUM_CGPA" name="POST_SEC_CUM_CGPA" type="text" class="form-control" value="">
			                                        <span class="bar"></span> 
			                                        <label for="POST_SEC_CUM_CGPA">Post-Sec. Cum. CGPA</label>
		                                    	</div>
		                                    </div>
		                                    <div class="d-flex">
		                                    	<div class="col-12 col-sm-4 form-group">
		                                    		<input id="SCHOOL_USER_DEFINED_4" name="SCHOOL_USER_DEFINED_4" type="text" class="form-control" value="">
			                                        <span class="bar"></span> 
			                                        <label for="SCHOOL_USER_DEFINED_4">School User Defined 4</label>
		                                    	</div>
		                                    	<div class="col-12 col-sm-4 form-group">
		                                    		<input id="SCHOOL_USER_DEFINED_5" name="SCHOOL_USER_DEFINED_5" type="text" class="form-control" value="">
			                                        <span class="bar"></span> 
			                                        <label for="SCHOOL_USER_DEFINED_5">School User Defined 5</label>
		                                    	</div>
		                                    	<div class="col-12 col-sm-4 form-group">
		                                    		<input id="SCHOOL_USER_DEFINED_6" name="SCHOOL_USER_DEFINED_6" type="text" class="form-control" value="">
			                                        <span class="bar"></span> 
			                                        <label for="SCHOOL_USER_DEFINED_6">School User Defined 6</label>
		                                    	</div>	                                        
		                                    </div>
		                                    <div class="d-flex">
		                                    	<div class="col-12 col-sm-3 form-group">
		                                    		<select id="PREVIOUS_COLLEGE" name="PREVIOUS_COLLEGE" class="form-control">
		                                    			<option selected></option>
			                                        </select>
			                                        <span class="bar"></span> 
			                                         <label for="PREVIOUS_COLLEGE">Previous College</label>
		                                    	</div>
		                                    	<div class="col-12 col-sm-3 form-group">
		                                    		<select id="HIGHEST_LEVEL_OF_ED" name="HIGHEST_LEVEL_OF_ED" class="form-control">
		                                    			<option selected></option>
			                                        </select>
			                                        <span class="bar"></span> 
			                                         <label for="HIGHEST_LEVEL_OF_ED">Highest level of Ed.</label>
		                                    	</div>
		                                    	<div class="col-12 col-sm-4 form-group">
		                                    		<input class="form-control" type="date" value="2011-08-19" name="EXPECTED_GRAD_DATE" id="EXPECTED_GRAD_DATE">
			                                        <span class="bar"></span> 
			                                        <label for="EXPECTED_GRAD_DATE">Expected Grad Date</label>
		                                    	</div>
	                                    	</div>
	                                    	<div class="d-flex">
		                                    	<div class="col-12 col-sm-3 form-group">
		                                    		<select id="SESSION" name="SESSION" class="form-control">
		                                    			<option selected></option>
			                                        </select>
			                                        <span class="bar"></span> 
			                                         <label for="SESSION">Session</label>
		                                    	</div>
		                                    	<div class="col-12 col-sm-3 form-group">
		                                    		<select id="FULL_PART_TIME" name="FULL_PART_TIME" class="form-control">
		                                    			<option selected></option>
			                                        </select>
			                                        <span class="bar"></span> 
			                                         <label for="FULL_PART_TIME">Full/Part Time</label>
		                                    	</div>
		                                    	<div class="col-12 col-sm-3 form-group">
		                                    		<select id="STUDENT_GROUP" name="STUDENT_GROUP" class="form-control">
		                                    			<option selected></option>
			                                        </select>
			                                        <span class="bar"></span> 
			                                         <label for="STUDENT_GROUP">Student Group</label>
		                                    	</div>	                                        
		                                    </div>
		                                    <div class="d-flex">
		                                    	<div class="col-12 col-sm-3 form-group">
		                                    		<input class="form-control" type="date" value="2011-08-19" name="DATE_OF_BIRTH" id="DATE_OF_BIRTH">
			                                        <span class="bar"></span> 
			                                        <label for="DATE_OF_BIRTH">Date Of Birth</label>
		                                    	</div>
		                                    	<div class="col-12 col-sm-3 form-group">
		                                    		<select id="GENDER" name="GENDER" class="form-control">
		                                    			<option selected></option>
			                                        </select>
			                                        <span class="bar"></span> 
			                                         <label for="GENDER">Gender</label>
		                                    	</div>
		                                    	<div class="col-12 col-sm-3 form-group">
		                                    		<input id="IPEDS" name="IPEDS" type="text" class="form-control" value="">
			                                        <span class="bar"></span> 
			                                         <label for="IPEDS">IPEDS Ethnicity</label>
		                                    	</div>
		                                    	<div class="col-12 col-sm-3 form-group">
		                                    		<input id="DRIVERS_LICENSE" name="DRIVERS_LICENSE" type="text" class="form-control" value="">
			                                        <span class="bar"></span> 
			                                         <label for="DRIVERS_LICENSE">Drivers License</label>
		                                    	</div>
	                                    	</div>
	                                    	<div class="d-flex">
		                                    	<div class="col-12 col-sm-3 form-group">
		                                    		<select id="FERPA_BLOCK" name="FERPA_BLOCK" class="form-control">
		                                    			<option selected></option>
			                                        </select>
			                                        <span class="bar"></span> 
			                                         <label for="FERPA_BLOCK">FERPA Block</label>
		                                    	</div>
		                                    	<div class="col-12 col-sm-3 form-group">
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
		                                    	<div class="col-12 col-sm-3 form-group">
		                                    		<input id="STUDENT_ID" name="STUDENT_ID" type="text" class="form-control" value="">
			                                        <span class="bar"></span> 
			                                         <label for="STUDENT_ID">Student ID</label>
		                                    	</div>
		                                    	<div class="col-12 col-sm-3 form-group">
		                                    		<select id="CAMPUS" name="CAMPUS" class="form-control">
		                                    			<option selected></option>
			                                        </select>
			                                        <span class="bar"></span> 
			                                         <label for="CAMPUS">Campus</label>
		                                    	</div>
		                                    </div>
		                                    <div class="d-flex">
		                                    	<div class="col-12 col-sm-4 form-group">
		                                    		<input id="COUNTRY_CITIZEN" name="COUNTRY_CITIZEN" type="text" class="form-control" value="">
			                                        <span class="bar"></span> 
			                                         <label for="COUNTRY_CITIZEN">Country/Citizen</label>
		                                    	</div>
		                                    	<div class="col-12 col-sm-4 form-group">
		                                    		<select id="US_CITIZEN" name="US_CITIZEN" class="form-control">
		                                    			<option selected></option>
		                                    			<? $res_type = $db->Execute("select * from Z_CITIZENSHIP order by CITIZENSHIP ASC");
														while (!$res_type->EOF) { ?>
															<option value="<?=$res_type->fields['PK_CITIZENSHIP']?>"><?=$res_type->fields['CITIZENSHIP']?></option>
														<?	$res_type->MoveNext();
														} ?>
			                                        </select>
			                                        <span class="bar"></span> 
			                                        <label for="US_CITIZEN">Citizenship</label>
		                                    	</div>
		                                    	<div class="col-12 col-sm-4 form-group">
		                                    		<input id="PLACE_OF_BIRTH" name="PLACE_OF_BIRTH" type="text" class="form-control" value="">
			                                        <span class="bar"></span> 
			                                        <label for="PLACE_OF_BIRTH">Place Of Birth</label>
		                                    	</div>		                                        
		                                    </div>
		                                    <div class="d-flex">
		                                    	<div class="col-12 col-sm-4 form-group">
		                                    		<input class="form-control" type="date" value="2011-08-19" id="CONTRACT_SIGNED_DATE" name="CONTRACT_SIGNED_DATE">
			                                        <span class="bar"></span> 
			                                        <label for="CONTRACT_SIGNED_DATE">Contract Signed Date</label>
		                                    	</div>
		                                    	<div class="col-12 col-sm-4 form-group">
		                                    		<input class="form-control" type="date" value="2011-08-19" id="CONTRACT_END_DATE" name="CONTRACT_END_DATE">
			                                        <span class="bar"></span> 
			                                        <label for="CONTRACT_END_DATE">Contract End Date</label>
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
					}		
				}).responseText;
			});
		}
	</script>

</body>

</html>