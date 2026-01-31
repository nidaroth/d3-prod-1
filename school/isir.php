<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/isir.php");
require_once("check_access.php");

if(check_access('MANAGEMENT_FINANCE') == 0 && check_access('FINANCE_ACCESS') == 0){
	header("location:../index");
	exit;
}
/*if(!empty($_POST)){
	$ISIR_STUDENT_MASTER['PK_STUDENT_MASTER']  = $_POST['PK_STUDENT_MASTER'];
	db_perform('S_ISIR_STUDENT_MASTER', $ISIR_STUDENT_MASTER, 'update'," PK_ISIR_STUDENT_MASTER = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' "); 
	header("location:isir?id=".$_GET['id']."&iid=".$_GET['iid']."&sid=".$_GET['sid']);
	exit;
}*/

$res_type = $db->Execute("select PK_STUDENT_MASTER from S_ISIR_STUDENT_MASTER WHERE PK_ISIR_STUDENT_MASTER = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
$PK_STUDENT_MASTER = $res_type->fields['PK_STUDENT_MASTER'];

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
	<link href="../backend_assets/node_modules/Magnific-Popup-master/dist/magnific-popup.css" rel="stylesheet">
	<link href="../backend_assets/dist/css/pages/user-card.css" rel="stylesheet">
	<title><?=ISIR_PAGE_TITLE?> | <?=$title?></title>
	<style>
		#advice-validate-one-required-by-name-PK_DOCUMENT_TYPE{position: absolute;top: 24px;}
	</style>
</head>

<body class="horizontal-nav boxed skin-megna fixed-layout">
   <? require_once("pre_load.php"); ?>
    <div id="main-wrapper">
       <? require_once("menu.php"); ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                 <div class="row page-titles">
                    <div class="col-md-2 align-self-center">
                        <h4 class="text-themecolor"><?=ISIR_PAGE_TITLE?></h4>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                           <div class="p-20 ">
								<div class="row">
									<div class="col-sm-12">
										<!--<form class="floating-labels m-t-40" method="post" name="form1" id="form1" >
											<div class="row">
												<div class="col-md-4">
													<div class="form-group m-b-40">
														<select id="PK_STUDENT_MASTER" name="PK_STUDENT_MASTER" class="form-control" >
															<option selected></option>
															<? /*$res_type = $db->Execute("select PK_STUDENT_MASTER, CONCAT(LAST_NAME,', ',FIRST_NAME) as NAME from S_STUDENT_MASTER WHERE ACTIVE = 1 AND ARCHIVED = 0 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by CONCAT(LAST_NAME,', ',FIRST_NAME) ASC");
															while (!$res_type->EOF) { ?>
																<option value="<?=$res_type->fields['PK_STUDENT_MASTER']?>" <? if($PK_STUDENT_MASTER == $res_type->fields['PK_STUDENT_MASTER']) echo "selected"; ?> ><?=$res_type->fields['NAME']?></option>
															<?	$res_type->MoveNext();
															}*/ ?>
														</select>
														
														<span class="bar"></span>
														<label for="ITEM"><?=STUDENT?></label>
													</div>
												</div>
											
												<div class="col-md-2">
													<div class="form-group m-b-5" >
														<button type="submit" class="btn waves-effect waves-light btn-info"><?=SAVE?></button>
													</div>
												</div>
											</div>
										</form>
										-->
										<table class="table table-hover">
											<thead>
												<tr>
													<th width="50%" ><?=FIELDS?></th>
													<th><?=VALUE?></th>
												</tr>
											</thead>
											<tbody>
												<? $res_det = $db->Execute("select PK_ISIR_SETUP_DETAIL, HEADING, DSIS_FIELD_NAME from Z_ISIR_SETUP_DETAIL WHERE ACTIVE = 1 AND PK_ISIR_SETUP_MASTER = '$_GET[iid]' ");
												while (!$res_det->EOF) { 
													$PK_ISIR_SETUP_DETAIL = $res_det->fields['PK_ISIR_SETUP_DETAIL']; 
													$res = $db->Execute("select VALUE from S_ISIR_STUDENT_DETAIL WHERE ACTIVE = 1 AND PK_ISIR_STUDENT_MASTER = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_ISIR_SETUP_DETAIL = '$PK_ISIR_SETUP_DETAIL' "); ?>
												<tr>
													<th><?=$res_det->fields['HEADING']?></th>
													<td>
														<? if($res_det->fields['DSIS_FIELD_NAME'] == "S_STUDENT_MASTER.DATE_OF_BIRTH") {
															if($res->fields['VALUE'] != '')
																echo date("m/d/Y",strtotime($res->fields['VALUE']));
														} else if($res_det->fields['DSIS_FIELD_NAME'] == "S_STUDENT_MASTER.SSN") {
															if($res->fields['VALUE'] != '')
																echo my_decrypt("",$res->fields['VALUE']);
														} else
															echo $res->fields['VALUE'];?>
													</td>
												</tr>
												<? $res_det->MoveNext();
												} ?>
											</tbody>
										</table>
									</div>
								</div>
										
	                            <div>
	                            	<a href="manage_isir?id=<?=$_GET['sid']?>" class="btn waves-effect waves-light btn-dark"><?=BACK?></a>
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
	
</body>

</html>