<? require_once("../global/config.php");
require_once("../language/common.php");
require_once("../language/ipeds_fall_collections_setup.php");
require_once("check_access.php");

if(check_access('MANAGEMENT_IPEDS') == 0 ){
	header("location:../index");
	exit;
}
if(!empty($_POST)){
	//echo "<pre>";print_r($_POST);exit;
	$i = 0;
	foreach($_POST['PK_CAMPUS_PROGRAM'] as $PK_CAMPUS_PROGRAM){
		$CAMPUS_PROGRAM['PK_IPEDES_PROGRAM_AWARD_LEVEL'] = $_POST['PK_IPEDES_PROGRAM_AWARD_LEVEL'][$i];
		db_perform('M_CAMPUS_PROGRAM', $CAMPUS_PROGRAM, 'update'," PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_CAMPUS_PROGRAM = '$PK_CAMPUS_PROGRAM' ");
		
		$i++;
	} ?>
	<script>window.close()</script>
<? } ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
	<? require_once("css.php"); ?>
	<title><?=PROGRAM_AWARD_LEVEL?> | <?=$title?></title>
</head>

<body class="horizontal-nav boxed skin-megna fixed-layout">
   <? require_once("pre_load.php"); ?>
    <div id="main-wrapper">
       <? //require_once("menu.php"); ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                 <div class="row page-titles">
                    <div class="col-md-3 align-self-center">
                        <h4 class="text-themecolor">
							<?=PROGRAM_AWARD_LEVEL ?> 
						</h4>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
								<form class="floating-labels " method="post" name="form1" id="form1" enctype="multipart/form-data" >
									<table class="table table-hover" >
										<thead>
											<tr>
												<th width="30%"><?=PROGRAM_CODE?></th>
												<th width="50%"><?=AWARD_LEVEL?></th>
												<th width="15%"><?=CREDENTIAL_LEVEL?></th>
												<th width="5%"><?=ACTIVE?></th>
											</tr>
										</thead>
										<tbody>
										<? $res_type = $db->Execute("SELECT PK_CAMPUS_PROGRAM,M_CAMPUS_PROGRAM.CODE AS PROG_CODE, M_CAMPUS_PROGRAM.DESCRIPTION, IF(M_CAMPUS_PROGRAM.ACTIVE = 1,'Yes','No') as ACTIVE_1, M_CREDENTIAL_LEVEL.CODE as CREDENTIAL_LEVEL, PK_IPEDES_PROGRAM_AWARD_LEVEL FROM M_CAMPUS_PROGRAM LEFT JOIN M_CREDENTIAL_LEVEL ON M_CREDENTIAL_LEVEL.PK_CREDENTIAL_LEVEL = M_CAMPUS_PROGRAM.PK_CREDENTIAL_LEVEL WHERE M_CAMPUS_PROGRAM.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ORDER BY M_CAMPUS_PROGRAM.ACTIVE DESC, M_CAMPUS_PROGRAM.CODE ASC, DESCRIPTION ASC ");
										while (!$res_type->EOF) { 
											$PK_IPEDES_PROGRAM_AWARD_LEVEL = $res_type->fields['PK_IPEDES_PROGRAM_AWARD_LEVEL']; ?>
											<tr>
												<td>
													<?=$res_type->fields['PROG_CODE'].' - '.$res_type->fields['DESCRIPTION'] ?>
													<input type="hidden" name="PK_CAMPUS_PROGRAM[]" value="<?=$res_type->fields['PK_CAMPUS_PROGRAM']?>" >
												</td>
												<td>
													<select id="PK_IPEDES_PROGRAM_AWARD_LEVEL" name="PK_IPEDES_PROGRAM_AWARD_LEVEL[]" class="form-control">
														<option></option>
														<? $res_type_1 = $db->Execute("select PK_IPEDES_PROGRAM_AWARD_LEVEL, IPEDES_PROGRAM_AWARD_LEVEL from M_IPEDES_PROGRAM_AWARD_LEVEL WHERE  ACTIVE = 1 order by IPEDES_PROGRAM_AWARD_LEVEL ASC");
														while (!$res_type_1->EOF) { ?>
															<option value="<?=$res_type_1->fields['PK_IPEDES_PROGRAM_AWARD_LEVEL']?>" <? if($PK_IPEDES_PROGRAM_AWARD_LEVEL == $res_type_1->fields['PK_IPEDES_PROGRAM_AWARD_LEVEL']) echo "selected"; ?> ><?=$res_type_1->fields['IPEDES_PROGRAM_AWARD_LEVEL'] ?></option>
														<?	$res_type_1->MoveNext();
														} ?>
													</select>
												</td>
												<td><?=$res_type->fields['CREDENTIAL_LEVEL']?></td>
												<td><?=$res_type->fields['ACTIVE_1']?></td>
											</tr>
										<?	$res_type->MoveNext();
										} ?>
										</tbody>
									</table>
									
									<div class="row">
										<div class="col-3 col-sm-3">
										</div>
										<div class="col-9 col-sm-9">
											<button type="submit" class="btn waves-effect waves-light btn-info"><?=SAVE?></button>
											<button type="button" onclick="javascript:window.close()"  class="btn waves-effect waves-light btn-dark"><?=CANCEL?></button>
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
   
	<? require_once("js.php"); ?>

</body>

</html>