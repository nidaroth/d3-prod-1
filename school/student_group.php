<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/student_group.php");
require_once("check_access.php");

if(check_access('SETUP_REGISTRAR') == 0 ){
	header("location:../index");
	exit;
}
if(!empty($_POST)){
	//echo "<pre>";print_r($_POST);exit;
	$STUDENT_GROUP = $_POST;
	if($_GET['id'] == ''){
		$STUDENT_GROUP['PK_ACCOUNT']  = $_SESSION['PK_ACCOUNT'];
		$STUDENT_GROUP['CREATED_BY']  = $_SESSION['PK_USER'];
		$STUDENT_GROUP['CREATED_ON']  = date("Y-m-d H:i");
		db_perform('M_STUDENT_GROUP', $STUDENT_GROUP, 'insert');
	} else {
		$STUDENT_GROUP['EDITED_BY'] = $_SESSION['PK_USER'];
		$STUDENT_GROUP['EDITED_ON'] = date("Y-m-d H:i");
		db_perform('M_STUDENT_GROUP', $STUDENT_GROUP, 'update'," PK_STUDENT_GROUP = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'");
	}
	header("location:manage_student_group");
}
if($_GET['id'] == ''){
	$STUDENT_GROUP 		= '';
	$PK_CAMPUS_PROGRAM 	= '';
	$NOTES 				= '';
	$ACTIVE  			= '';
} else {
	$res = $db->Execute("SELECT * FROM M_STUDENT_GROUP WHERE PK_STUDENT_GROUP = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' "); 
	if($res->RecordCount() == 0){
		header("location:manage_student_group");
		exit;
	}
	$STUDENT_GROUP 		= $res->fields['STUDENT_GROUP'];
	$PK_CAMPUS_PROGRAM 	= $res->fields['PK_CAMPUS_PROGRAM'];
	$NOTES 				= $res->fields['NOTES'];
	$ACTIVE  			= $res->fields['ACTIVE'];
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
	<title><?=STUDENT_GROUP_PAGE_TITLE?> | <?=$title?></title>
</head>

<body class="horizontal-nav boxed skin-megna fixed-layout">
   <? require_once("pre_load.php"); ?>
    <div id="main-wrapper">
       <? require_once("menu.php"); ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                 <div class="row page-titles">
                    <div class="col-md-12 align-self-center">
                        <h4 class="text-themecolor"><? if($_GET['id'] == '') echo ADD; else echo EDIT; ?> <?=STUDENT_GROUP_PAGE_TITLE?> </h4>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <form class="floating-labels m-t-40" method="post" name="form1" id="form1" >
								
									<div class="row">
                                        <div class="col-md-6">
											<div class="form-group m-b-40">
												<input type="text" class="form-control required-entry" id="STUDENT_GROUP" name="STUDENT_GROUP" value="<?=$STUDENT_GROUP?>" >
												<span class="bar"></span>
												<label for="STUDENT_GROUP"><?=STUDENT_GROUP?></label>
											</div>
										</div>
                                    </div>
									
									<div class="row">
                                        <div class="col-md-6">
											<div class="form-group m-b-40">
												<select id="PK_CAMPUS_PROGRAM" name="PK_CAMPUS_PROGRAM" class="form-control">
													<option></option>
													<? $res_type = $db->Execute("select PK_CAMPUS_PROGRAM,CODE,DESCRIPTION from M_CAMPUS_PROGRAM WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by CODE ASC");
													while (!$res_type->EOF) { ?>
														<option value="<?=$res_type->fields['PK_CAMPUS_PROGRAM']?>" <? if($PK_CAMPUS_PROGRAM == $res_type->fields['PK_CAMPUS_PROGRAM']) echo "selected"; ?> ><?=$res_type->fields['CODE'].' - '.$res_type->fields['DESCRIPTION']?></option>
													<?	$res_type->MoveNext();
													} ?>
												</select>
												<span class="bar"></span>
												<label for="PK_CAMPUS_PROGRAM"><?=PROGRAM?></label>
											</div>
										</div>
                                    </div>
								
									<div class="row">
										<div class="col-md-8">
											<div class="form-group m-b-40">
												<textarea class="form-control" rows="2" id="NOTES" name="NOTES"><?=$NOTES?></textarea>
												<span class="bar"></span>
												<label for="NOTES"><?=COMMENTS?></label>
											</div>
										</div>
									</div>
									
									<? if($_GET['id'] != ''){ ?>
									<div class="row">
										<div class="col-md-6">
											<div class="row form-group">
												<div class="custom-control col-md-2"><?=ACTIVE?></div>
												<div class="custom-control custom-radio col-md-1">
													<input type="radio" id="customRadio11" name="ACTIVE" value="1" <? if($ACTIVE == 1) echo "checked"; ?> class="custom-control-input">
													<label class="custom-control-label" for="customRadio11"><?=YES?></label>
												</div>
												<div class="custom-control custom-radio col-md-1">
													<input type="radio" id="customRadio22" name="ACTIVE" value="0" <? if($ACTIVE == 0) echo "checked"; ?>  class="custom-control-input">
													<label class="custom-control-label" for="customRadio22"><?=NO?></label>
												</div>
											</div>
										</div>
									</div>
									<? } ?>
									
									<div class="row">
                                        <div class="col-md-6">
											<div class="form-group m-b-5"  style="text-align:right" >
												<button type="submit" class="btn waves-effect waves-light btn-info"><?=SAVE?></button>
												
												<button type="button" class="btn waves-effect waves-light btn-dark" onclick="window.location.href='manage_student_group'" ><?=CANCEL?></button>
												
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
    </div>
   
	<? require_once("js.php"); ?>
	<script src="../backend_assets/dist/js/validation_prototype.js"></script>
	<script src="../backend_assets/dist/js/validation.js"></script>
	<script type="text/javascript">
		var form1 = new Validation('form1');
	</script>

</body>

</html>