<? 
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

ini_set("memory_limit","3000M");
ini_set("max_execution_time","600");

require_once("../global/config.php"); 
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
	<title>Active Users | <?=$title?></title>
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
                    <div class="col-md-12 align-self-center">
                        <h4 class="text-themecolor">Active Users</h4>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <form class="floating-labels " method="get" name="form1" id="form1" >
									<div class="row" style="padding-bottom:10px;" >
										<div class="col-md-2 ">
											School
											<select name="PK_ACCOUNT" id="PK_ACCOUNT" class="form-control" >
												<option value="">All Schools</option>
												<? $res_dep = $db->Execute("select PK_ACCOUNT,SCHOOL_NAME from Z_ACCOUNT WHERE ACTIVE = '1' ORDER BY SCHOOL_NAME ASC ");
												while (!$res_dep->EOF) { ?>
													<option value="<?=$res_dep->fields['PK_ACCOUNT']?>" <? if($_GET['PK_ACCOUNT'] == $res_dep->fields['PK_ACCOUNT']) echo "selected"; ?> ><?=$res_dep->fields['SCHOOL_NAME']?></option>
												<?	$res_dep->MoveNext();
												} 	?>
											</select>
										</div>
										
										<div class="col-md-2 ">
											User Type
											<select name="USER_TYPE" id="USER_TYPE" class="form-control" >
												<option value="">All User Type</option>
												<option value="1" <? if($_GET['USER_TYPE'] == 1) echo "selected"; ?> >School User</option>
												<option value="2" <? if($_GET['USER_TYPE'] == 2) echo "selected"; ?> >Faculty</option>
												<option value="3" <? if($_GET['USER_TYPE'] == 3) echo "selected"; ?> >Student</option>
											</select>
										</div>
										
										<div class="col-md-2 ">
											From Date
											<input type="text" class="form-control date" id="START_DATE" name="START_DATE" placeholder="" value="<?=$_GET['START_DATE']?>" >
										</div>
										
										<div class="col-md-2 ">
											To Date
											<input type="text" class="form-control date" id="TO_DATE" name="TO_DATE" placeholder="" value="<?=$_GET['TO_DATE']?>" >
										</div>
										
										<div class="col-md-4 ">
											<br />
											<button type="submit" class="btn waves-effect waves-light btn-info">Go</button>
											<? if(!empty($_GET)){ ?>
											<button type="button" onclick="generate_excel()" class="btn waves-effect waves-light btn-info">Export To Excel</button>
											<? } ?>
										</div>
									</div>	
									<? if(!empty($_GET)){
										$cond  = " Z_ACCOUNT.PK_ACCOUNT != 1 ";
										$where = " Z_ACTIVE_USERS.PK_ACCOUNT != 1 ";
										if($_GET['PK_ACCOUNT'] != '')
											$cond .= " AND Z_ACCOUNT.PK_ACCOUNT = '$_GET[PK_ACCOUNT]' ";
											
										$PK_ACCOUNT = $_GET['PK_ACCOUNT'];
										$USER_TYPE 	= $_GET['USER_TYPE'];
										$START_DATE = $_GET['START_DATE'];
										$TO_DATE 	= $_GET['TO_DATE'];

										if($START_DATE != '' && $TO_DATE != '') {
											$ST = date("Y-m-d",strtotime($START_DATE));
											$ET = date("Y-m-d",strtotime($TO_DATE));
											$where .= " AND DATE BETWEEN '$ST' AND '$ET' ";
										} else if($START_DATE != ''){
											$ST = date("Y-m-d",strtotime($START_DATE));
											$where .= " AND DATE >= '$ST' ";
										} else if($TO_DATE != ''){
											$ET = date("Y-m-d",strtotime($TO_DATE));
											$where .= " AND DATE <= '$ET' ";
										}

										if($PK_ACCOUNT != '')
											$where .= " AND Z_ACTIVE_USERS.PK_ACCOUNT = '$PK_ACCOUNT' ";

										if($USER_TYPE != '')
											$where .= " AND USER_TYPE  = '$USER_TYPE' ";
										?>
										<div class="row" style="padding-bottom:10px;" >
											<table class="table table-hover" style="width:70%" >
												<thead>
													<tr>
														<th>School Name</th>
														<? if($USER_TYPE == '' || $USER_TYPE == 1){ ?>
														<th style="text-align: center;" >School User</th>
														<? } ?>
														<? if($USER_TYPE == '' || $USER_TYPE == 2){ ?>
														<th style="text-align: center;" >Faculty</th>
														<? } ?>
														<? if($USER_TYPE == '' || $USER_TYPE == 3){ ?>
														<th style="text-align: center;" >Student</th>
														<? } ?>
														<? if($USER_TYPE == ''){ ?>
														<th style="text-align: center;" >School Total</th>
														<? } ?>
													</tr>
												</thead>
												<tbody>
												<? $total_school_user  = 0; 
												$total_school_faculty  = 0; 
												$total_school_student  = 0; 
												$total_school_all_user = 0; 
												$res_dep = $db->Execute("select PK_ACCOUNT,SCHOOL_NAME from Z_ACCOUNT WHERE $cond  ORDER BY SCHOOL_NAME ASC ");
												while (!$res_dep->EOF) { 
													$PK_ACCOUNT = $res_dep->fields['PK_ACCOUNT'];
													
													$res_sc_user = $db->Execute("SELECT PK_ACTIVE_USERS FROM Z_ACTIVE_USERS LEFT JOIN Z_ACCOUNT ON Z_ACCOUNT.PK_ACCOUNT = Z_ACTIVE_USERS.PK_ACCOUNT  WHERE $where AND Z_ACTIVE_USERS.PK_ACCOUNT = '$PK_ACCOUNT' AND USER_TYPE = '1' GROUP BY PK_USER");  
													$res_faculty = $db->Execute("SELECT PK_ACTIVE_USERS FROM Z_ACTIVE_USERS LEFT JOIN Z_ACCOUNT ON Z_ACCOUNT.PK_ACCOUNT = Z_ACTIVE_USERS.PK_ACCOUNT  WHERE $where AND Z_ACTIVE_USERS.PK_ACCOUNT = '$PK_ACCOUNT' AND USER_TYPE = '2' GROUP BY PK_USER");
													$res_student = $db->Execute("SELECT PK_ACTIVE_USERS FROM Z_ACTIVE_USERS LEFT JOIN Z_ACCOUNT ON Z_ACCOUNT.PK_ACCOUNT = Z_ACTIVE_USERS.PK_ACCOUNT  WHERE $where AND Z_ACTIVE_USERS.PK_ACCOUNT = '$PK_ACCOUNT' AND USER_TYPE = '3' GROUP BY PK_USER");
													
													$total_school 			= $res_sc_user->RecordCount() + $res_faculty->RecordCount() + $res_student->RecordCount();
													$total_school_user 	   += $res_sc_user->RecordCount(); 
													$total_school_faculty  += $res_faculty->RecordCount(); 
													$total_school_student  += $res_student->RecordCount(); 
													$total_school_all_user += $total_school; ?>
													<tr>
														<td><?=$res_dep->fields['SCHOOL_NAME']?></td>
														<? if($USER_TYPE == '' || $USER_TYPE == 1){ ?>
														<td align="center"><?=$res_sc_user->RecordCount()?></td>
														<? } ?>
														<? if($USER_TYPE == '' || $USER_TYPE == 2){ ?>
														<td align="center"><?=$res_faculty->RecordCount()?></td>
														<? } ?>
														<? if($USER_TYPE == '' || $USER_TYPE == 3){ ?>
														<td align="center"><?=$res_student->RecordCount()?></td>
														<? } ?>
														<? if($USER_TYPE == ''){ ?>
														<td align="center" ><?=$total_school?></td>
														<? } ?>
													</tr>
												<?	$res_dep->MoveNext();
												} ?>
													<tr>
														<th>Total</th>
														<? if($USER_TYPE == '' || $USER_TYPE == 1){ ?>
														<th style="text-align: center;" ><?=$total_school_user?></th>
														<? } ?>
														<? if($USER_TYPE == '' || $USER_TYPE == 2){ ?>
														<th style="text-align: center;" ><?=$total_school_faculty?></th>
														<? } ?>
														<? if($USER_TYPE == '' || $USER_TYPE == 3){ ?>
														<th style="text-align: center;" ><?=$total_school_student?></th>
														<? } ?>
														<? if($USER_TYPE == ''){ ?>
														<th style="text-align: center;" ><?=$total_school_all_user?></th>
														<? } ?>
													</tr>
												</tbody>
											</table>
										</div>
									<? } ?>
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
	
	<script src="../backend_assets/node_modules/bootstrap-datepicker/bootstrap-datepicker.min.js"></script>
	<link href="../backend_assets/node_modules/bootstrap-datepicker/bootstrap-datepicker.min.css" rel="stylesheet" type="text/css" />

	<script type="text/javascript">
	jQuery(document).ready(function($) { 
		jQuery('.date').datepicker({
			todayHighlight: true,
			orientation: "bottom auto"
		});
	});
	function generate_excel(){
		jQuery(document).ready(function($) {
			window.location.href = "active_user_excel.php?type="+$("#USER_TYPE").val()+"&acc="+$("#PK_ACCOUNT").val()+"&st="+$("#START_DATE").val()+"&ed="+$("#TO_DATE").val()
		});
	}
	</script>
</body>

</html>