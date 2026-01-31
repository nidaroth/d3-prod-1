<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/student_portal_user.php");
require_once("check_access.php");

if(check_access('MANAGEMENT_REGISTRAR') == 0 ){
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
	<title><?=VIEW_LOG?> | <?=$title?></title>
</head>

<body class="horizontal-nav boxed skin-megna fixed-layout">
   <? require_once("pre_load.php"); ?>
    <div id="main-wrapper">
       <? //require_once("menu.php"); ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                 <div class="row page-titles">
                    <div class="col-md-12 align-self-center">
						<? $res_type = $db->Execute("select CONCAT(S_STUDENT_MASTER.LAST_NAME,', ', S_STUDENT_MASTER.FIRST_NAME) AS STU_NAME FROM S_STUDENT_MASTER,Z_USER WHERE PK_USER = '$_GET[id]' AND S_STUDENT_MASTER.PK_STUDENT_MASTER = Z_USER.ID AND PK_USER_TYPE = 3 "); ?>
                        <h4 class="text-themecolor"><?=VIEW_LOG.' - '.$res_type->fields['STU_NAME']?> </h4>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <form class="floating-labels m-t-40" method="post" name="form1" id="form1" >
									
									<div class="table-responsive p-20">
										<table class="table table-hover" >
											<thead>
												<tr>
													<th><?=LOGIN?></th>
													<!--<th><?=LOGOUT?></th>-->
													<th><?=IP_ADDRESS?></th>
												</tr>
											</thead>
											<tbody>
												<? $res_type = $db->Execute("select IP_ADDRESS, LOGIN_TIME, LOGOUT_TIME FROM Z_LOGIN_HISTORY WHERE PK_USER = '$_GET[id]' ORDER BY LOGIN_TIME DESC ");
												while (!$res_type->EOF) { ?>
													<tr >
														<td>
															<? if($res_type->fields['LOGIN_TIME'] != '0000-00-00 00-00-00' && $res_type->fields['LOGIN_TIME'] != '')
																echo date("m/d/Y h:i A",strtotime($res_type->fields['LOGIN_TIME'])); ?>
														</td>
														<!--<td>
															<? if($res_type->fields['LOGOUT_TIME'] != '0000-00-00 00:00:00' && $res_type->fields['LOGOUT_TIME'] != '')
																echo date("m/d/Y h:i A",strtotime($res_type->fields['LOGOUT_TIME'])); ?>
														</td>-->
														<td><?=$res_type->fields['IP_ADDRESS']?></td>
													</tr>
												<?	$res_type->MoveNext();
												} ?>
											</tbody>
										</table>
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
   
	<? require_once("js.php"); ?>
	<script src="../backend_assets/dist/js/validation_prototype.js"></script>
	<script src="../backend_assets/dist/js/validation.js"></script>
	<script type="text/javascript">
		var form1 = new Validation('form1');
	</script>
	
</body>

</html>