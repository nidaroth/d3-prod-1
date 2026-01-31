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
	<title>Dummy | <?=$title?></title>
</head>

<body class="horizontal-nav boxed skin-megna fixed-layout">
   <? require_once("pre_load.php"); ?>
    <div id="main-wrapper">
       <? require_once("menu.php"); ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                 <div class="row page-titles">
                    <div class="col-md-5 align-self-center">
                        <h4 class="text-themecolor">Dummy</h4>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="card">
							<ul class="nav nav-tabs customtab" role="tablist">
                                <li class="nav-item"> <a class="nav-link active" data-toggle="tab" href="#generalTab" role="tab"><span class="hidden-sm-up"><i class="ti-home"></i></span> <span class="hidden-xs-down">General</span></a> </li>
                                <li class="nav-item"> <a class="nav-link" data-toggle="tab" href="#campusTab" role="tab"><span class="hidden-sm-up"><i class="ti-user"></i></span> <span class="hidden-xs-down">Campus</span></a> </li>
                                <li class="nav-item"> <a class="nav-link" data-toggle="tab" href="#dspsTab" role="tab"><span class="hidden-sm-up"><i class="ti-email"></i></span> <span class="hidden-xs-down">DSPS</span></a> </li>
                            </ul>
                            <!-- Tab panes -->
                            <div class="tab-content">
                                <div class="tab-pane active" id="generalTab" role="tabpanel">
                                    <div class="p-20">
                                        <form class="floating-labels">
		                                    <div class="form-group">
		                                        <input id="SCHOOL_ID" name="SCHOOL_ID" type="text" class="form-control" value="">
		                                        <span class="bar"></span> 
		                                         <label for="SCHOOL_ID">School ID</label>
		                                    </div>
		                                    <div class="form-group">
		                                        <input id="SCHOOL_NAME" name="SCHOOL_NAME" type="text" class="form-control" value="">
		                                        <span class="bar"></span> 
		                                         <label for="SCHOOL_NAME">School Name</label>
		                                    </div>
		                                    <div class="form-group">
		                                        <input id="SCHOOL_CODE" name="SCHOOL_CODE" type="text" class="form-control" value="">
		                                        <span class="bar"></span> 
		                                         <label for="SCHOOL_CODE">School Code</label>
		                                    </div>
		                                    <div class="form-group">
		                                        <input id="INSTITUTION_CODE" name="INSTITUTION_CODE" type="text" class="form-control" value="">
		                                        <span class="bar"></span> 
		                                         <label for="INSTITUTION_CODE">Institution Code(OPEID)</label>
		                                    </div>
		                                    <div class="form-group">
		                                        <input id="FEDERAL_SCHOOL_CODE" name="FEDERAL_SCHOOL_CODE" type="text" class="form-control" value="">
		                                        <span class="bar"></span> 
		                                         <label for="FEDERAL_SCHOOL_CODE">Federal School Code</label>
		                                    </div>
		                                    <div class="form-group">
		                                        <input id="AMBASSADOR_SCHOOL_CODE" name="AMBASSADOR_SCHOOL_CODE" type="text" class="form-control" value="">
		                                        <span class="bar"></span> 
		                                         <label for="AMBASSADOR_SCHOOL_CODE">Ambassador School Code</label>
		                                    </div>
		                                    <div class="form-group">
		                                        <input id="COSMO_LICENSE" name="COSMO_LICENSE" type="text" class="form-control" value="">
		                                        <span class="bar"></span> 
		                                         <label for="COSMO_LICENSE">Cosmo License</label>
		                                    </div>
		                                    <div class="form-group">
		                                        <textarea class="form-control" rows="2" id="ADDRESS" name="ADDRESS"></textarea>
		                                        <span class="bar"></span>
		                                        <label for="ADDRESS">Address</label>
		                                    </div>
		                                    <div class="form-group">
		                                        <textarea class="form-control" rows="2" id="ADDRESS1" name="ADDRESS1"></textarea>
		                                        <span class="bar"></span>
		                                        <label for="ADDRESS1">Address 2</label>
		                                    </div>
		                                    <div class="form-group">
		                                        <input id="CITY" name="CITY" type="text" class="form-control" value="">
		                                        <span class="bar"></span> 
		                                         <label for="CITY">City</label>
		                                    </div>
		                                    <div class="form-group">
		                                        <input id="STATE" name="STATE" type="text" class="form-control" value="">
		                                        <span class="bar"></span> 
		                                         <label for="STATE">State</label>
		                                    </div>
		                                    <div class="form-group">
		                                        <input id="ZIP" name="ZIP" type="text" class="form-control" value="">
		                                        <span class="bar"></span> 
		                                         <label for="ZIP">Zip</label>
		                                    </div>
		                                    <div class="form-group">
		                                        <input id="PHONE" name="PHONE" type="text" class="form-control" value="">
		                                        <span class="bar"></span> 
		                                         <label for="PHONE">Phone</label>
		                                    </div>
		                                    <div class="form-group">
		                                        <input id="FAX" name="FAX" type="text" class="form-control" value="">
		                                        <span class="bar"></span> 
		                                         <label for="FAX">Fax</label>
		                                    </div>
		                                    <div class="form-group">
		                                        <input id="EMAIL" name="EMAIL" type="text" class="form-control" value="">
		                                        <span class="bar"></span> 
		                                         <label for="EMAIL">Email</label>
		                                    </div>
		                                    <div class="form-group">
		                                        <input id="WEBSITE" name="WEBSITE" type="text" class="form-control" value="">
		                                        <span class="bar"></span> 
		                                         <label for="WEBSITE">Website</label>
		                                    </div>
		                                    <div class="form-group">
		                                        <select id="EARNINGS_TYPE" name="EARNINGS_TYPE" class="form-control">
		                                            <? $res_type = $db->Execute("select PK_EARNING_TYPE, EARNING_TYPE from M_EARNING_TYPE WHERE ACTIVE = '1'");
													while (!$res_type->EOF) { ?>
														<option value="<?=$res_type->fields['EARNING_TYPE'] ?>" selected ><?=$res_type->fields['EARNING_TYPE']?></option>
													<?	$res_type->MoveNext();
													} ?>
		                                        </select>
		                                        <label for="EARNINGS_TYPE">Earnings Type</label>
		                                    </div>
		                                    <div class="form-group">
		                                        <input id="UNIT_COST" name="UNIT_COST" type="text" class="form-control" value="">
		                                        <span class="bar"></span> 
		                                         <label for="UNIT_COST">Unit Cost</label>
		                                    </div>
		                                    <div class="form-group">
		                                        <input id="TERM_COST" name="TERM_COST" type="text" class="form-control" value="">
		                                        <span class="bar"></span> 
		                                         <label for="TERM_COST">Term Cost</label>
		                                    </div>
		                                    <div class="form-group">
		                                        <input id="CLIENT_ID" name="CLIENT_ID" type="text" class="form-control" value="">
		                                        <span class="bar"></span> 
		                                         <label for="CLIENT_ID">Client ID</label>
		                                    </div>
		                                    <div class="form-group">
		                                        <button type="submit" class="btn btn-success mr-2">Submit</button>
		                                        <button type="submit" class="btn btn-dark">Cancel</button>
		                                    </div>
		                                </form>
                                    </div>
                                </div>
                                <div class="tab-pane" id="campusTab" role="tabpanel">
                                	<div class="p-20">
                                        <form class="floating-labels">
		                                    <div class="form-group">
		                                        <input id="OFFICIAL_CAMPUS_NAME" name="OFFICIAL_CAMPUS_NAME" type="text" class="form-control" value="">
		                                        <span class="bar"></span> 
		                                         <label for="OFFICIAL_CAMPUS_NAME">Official Campus Name</label>
		                                    </div>
		                                    <div class="form-group">
		                                        <input id="CAMPUS_NAME" name="CAMPUS_NAME" type="text" class="form-control" value="">
		                                        <span class="bar"></span> 
		                                         <label for="CAMPUS_NAME">Campus Name</label>
		                                    </div>
		                                    <div class="form-group">
		                                        <input id="CAMPUS_CODE" name="CAMPUS_CODE" type="text" class="form-control" value="">
		                                        <span class="bar"></span> 
		                                         <label for="CAMPUS_CODE">Campus Code</label>
		                                    </div>
		                                    <div class="form-group">
		                                        <input id="INSTITUTION_CODE_CAMPUS" name="INSTITUTION_CODE_CAMPUS" type="text" class="form-control" value="">
		                                        <span class="bar"></span> 
		                                         <label for="INSTITUTION_CODE_CAMPUS">Institution Code(OPEID)</label>
		                                    </div>
		                                    <div class="form-group">
		                                        <select id="DEFAULT_MODULE_CAMPUS" name="DEFAULT_MODULE_CAMPUS" class="form-control">
		                                           <option>1</option>
		                                           <option>2</option>
		                                           <option>3</option>
		                                           <option>4</option>
		                                        </select>
		                                        <label for="DEFAULT_MODULE_CAMPUS">Default Module Campus</label>
		                                    </div>
		                                    <div class="form-group">
		                                        <div class="custom-control custom-checkbox mr-sm-2">
		                                            <input type="checkbox" class="custom-control-input" id="IS_PRIMARY_CAMPUS" value="check">
		                                            <label class="custom-control-label" for="IS_PRIMARY_CAMPUS">Primary Campus?</label>
		                                        </div>
		                                    </div>
		                                    <div class="form-group">
		                                        <textarea class="form-control" rows="2" id="ADDRESS" name="ADDRESS"></textarea>
		                                        <span class="bar"></span>
		                                        <label for="ADDRESS">Address</label>
		                                    </div>
		                                    <div class="form-group">
		                                        <textarea class="form-control" rows="2" id="ADDRESS1" name="ADDRESS1"></textarea>
		                                        <span class="bar"></span>
		                                        <label for="ADDRESS1">Address 2</label>
		                                    </div>
		                                    <div class="form-group">
		                                        <input id="CITY" name="CITY" type="text" class="form-control" value="">
		                                        <span class="bar"></span> 
		                                         <label for="CITY">City</label>
		                                    </div>
		                                    <div class="form-group">
		                                        <input id="STATE" name="STATE" type="text" class="form-control" value="">
		                                        <span class="bar"></span> 
		                                         <label for="STATE">State</label>
		                                    </div>
		                                    <div class="form-group">
		                                        <input id="PHONE" name="PHONE" type="text" class="form-control" value="">
		                                        <span class="bar"></span> 
		                                         <label for="PHONE">Phone</label>
		                                    </div>
		                                    <div class="form-group">
		                                        <input id="FAX" name="FAX" type="text" class="form-control" value="">
		                                        <span class="bar"></span> 
		                                         <label for="FAX">Fax</label>
		                                    </div>
		                                    <div class="form-group">
		                                        <input id="ACCSC_SCHOOL_NUMBER" name="ACCSC_SCHOOL_NUMBER" type="text" class="form-control" value="">
		                                        <span class="bar"></span> 
		                                         <label for="ACCSC_SCHOOL_NUMBER">ACCSC School Number</label>
		                                    </div>
		                                    <div class="form-group">
		                                        <input id="ACICS_SCHOOL_NUMBER" name="ACICS_SCHOOL_NUMBER" type="text" class="form-control" value="">
		                                        <span class="bar"></span> 
		                                         <label for="ACICS_SCHOOL_NUMBER">ACICS School Number</label>
		                                    </div>
		                                    <div class="form-group">
		                                        <input id="NACCAS_SCHOOL_NUMBER" name="NACCAS_SCHOOL_NUMBER" type="text" class="form-control" value="">
		                                        <span class="bar"></span> 
		                                         <label for="NACCAS_SCHOOL_NUMBER">NACCAS School Number</label>
		                                    </div>
		                                    <div class="form-group">
		                                        <button type="submit" class="btn btn-success mr-2">Submit</button>
		                                        <button type="submit" class="btn btn-dark">Cancel</button>
		                                    </div>
		                                </form>
                                    </div>
                                </div>
                                <div class="tab-pane" id="dspsTab" role="tabpanel">
                                	<div class="table-responsive p-20">
	                                    <table class="table table-hover">
	                                        <thead>
	                                            <tr>
	                                                <th>#</th>
	                                                <th>Name</th>
	                                                <th>Role</th>
	                                                <th>Email</th>
	                                                <th>Phone</th>
	                                                <th>Options</th>
	                                            </tr>
	                                        </thead>
	                                        <tbody>
	                                            <tr>
	                                                <td>1</td>
	                                                <td>John</td>
	                                                <td>Admin</td>
	                                                <td>john@example.com</td>
	                                                <td>0458 855586</td>
	                                                <td>
	                                                	<a href="javascript:void(0);" title="Edit" class="btn btn-secondary btn-circle"><i class="far fa-edit"></i> </a>
	                                                	<a href="javascript:void(0);" title="Delete" class="btn btn-primary btn-circle"><i class="far fa-trash-alt"></i> </a>
	                                                </td>
	                                            </tr>
	                                            <tr>
	                                                <td>2</td>
	                                                <td>Bell</td>
	                                                <td>Manager</td>
	                                                <td>bell@example.com</td>
	                                                <td>0458 788598</td>
	                                                <td>
	                                                	<a href="javascript:void(0);" title="Edit" class="btn btn-secondary btn-circle"><i class="far fa-edit"></i> </a>
	                                                	<a href="javascript:void(0);" title="Delete" class="btn btn-primary btn-circle"><i class="far fa-trash-alt"></i> </a>
	                                                </td>
	                                            </tr>
	                                            <tr>
	                                                <td>3</td>
	                                                <td>Balaji</td>
	                                                <td>Owner</td>
	                                                <td>balaji@example.com</td>
	                                                <td>0458 225554</td>
	                                                <td>
	                                                	<a href="javascript:void(0);" title="Edit" class="btn btn-secondary btn-circle"><i class="far fa-edit"></i> </a>
	                                                	<a href="javascript:void(0);" title="Delete" class="btn btn-primary btn-circle"><i class="far fa-trash-alt"></i> </a>
	                                                </td>
	                                            </tr>
	                                            <tr>
	                                                <td>4</td>
	                                                <td>Karma</td>
	                                                <td>Super Admin</td>
	                                                <td>karma@example.com</td>
	                                                <td>0458 454545</td>
	                                                <td>
	                                                	<a href="javascript:void(0);" title="Edit" class="btn btn-secondary btn-circle"><i class="far fa-edit"></i> </a>
	                                                	<a href="javascript:void(0);" title="Delete" class="btn btn-primary btn-circle"><i class="far fa-trash-alt"></i> </a>
	                                                </td>
	                                            </tr>
	                                        </tbody>
	                                    </table>
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
	</script>

</body>

</html>