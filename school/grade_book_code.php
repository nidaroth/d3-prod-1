<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/grade_book_code.php");
require_once("check_access.php");

if(check_access('SETUP_REGISTRAR') == 0 ){
	header("location:../index");
	exit;
}
if(!empty($_POST)){
	//echo "<pre>";print_r($_POST);exit;
	$GRADE_BOOK_CODE = $_POST;
	if($_GET['id'] == ''){
		$GRADE_BOOK_CODE['PK_ACCOUNT']  = $_SESSION['PK_ACCOUNT'];
		$GRADE_BOOK_CODE['CREATED_BY']  = $_SESSION['PK_USER'];
		$GRADE_BOOK_CODE['CREATED_ON']  = date("Y-m-d H:i");
		db_perform('M_GRADE_BOOK_CODE', $GRADE_BOOK_CODE, 'insert');
	} else {
		$GRADE_BOOK_CODE['EDITED_BY'] = $_SESSION['PK_USER'];
		$GRADE_BOOK_CODE['EDITED_ON'] = date("Y-m-d H:i");
		db_perform('M_GRADE_BOOK_CODE', $GRADE_BOOK_CODE, 'update'," PK_GRADE_BOOK_CODE = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'");
	}
	header("location:manage_grade_book_code");
}
if($_GET['id'] == ''){
	$CODE 				= '';
	$DESCRIPTION		= '';
	$HOUR				= '';
	$SESSIONS			= '';
	$POINTS				= '';
	$ACTIVE	 			= '';
	$PK_GRADE_BOOK_TYPE = '';
	
} else {
	$res = $db->Execute("SELECT * FROM M_GRADE_BOOK_CODE WHERE PK_GRADE_BOOK_CODE = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' "); 
	if($res->RecordCount() == 0){
		header("location:manage_grade_book_code");
		exit;
	}
	
	$CODE 				= $res->fields['CODE'];
	$DESCRIPTION 		= $res->fields['DESCRIPTION'];
	$PK_GRADE_BOOK_TYPE = $res->fields['PK_GRADE_BOOK_TYPE'];
	$HOUR 				= $res->fields['HOUR'];
	$SESSIONS 			= $res->fields['SESSIONS'];
	$POINTS 			= $res->fields['POINTS'];
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
	<title><?=GRADE_BOOK_CODE_PAGE_TITLE?> | <?=$title?></title>
</head>

<body class="horizontal-nav boxed skin-megna fixed-layout">
   <? require_once("pre_load.php"); ?>
    <div id="main-wrapper">
       <? require_once("menu.php"); ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                 <div class="row page-titles">
                    <div class="col-md-12 align-self-center">
                        <h4 class="text-themecolor"><? if($_GET['id'] == '') echo ADD; else echo EDIT; ?> <?=GRADE_BOOK_CODE_PAGE_TITLE?> </h4>
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
												<input type="text" class="form-control required-entry" id="CODE" name="CODE" value="<?=$CODE?>" >
												<span class="bar"></span>
												<label for="CODE"><?=GRADE_BOOK_CODE?></label>
											</div>
										</div>
                                    </div>
									
									<div class="row">
                                        <div class="col-md-6">
											<div class="form-group m-b-40">
												<select id="PK_GRADE_BOOK_TYPE" name="PK_GRADE_BOOK_TYPE" class="form-control" >
													<option ></option>
													<? $res_cs = $db->Execute("SELECT PK_GRADE_BOOK_TYPE, GRADE_BOOK_TYPE FROM M_GRADE_BOOK_TYPE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ACTIVE = 1 ORDER BY GRADE_BOOK_TYPE ASC");
													while (!$res_cs->EOF) { ?>
														<option value="<?=$res_cs->fields['PK_GRADE_BOOK_TYPE']?>" <? if($PK_GRADE_BOOK_TYPE == $res_cs->fields['PK_GRADE_BOOK_TYPE'] ) echo "selected";?> ><?=$res_cs->fields['GRADE_BOOK_TYPE'] ?></option>
													<?	$res_cs->MoveNext();
													} ?>
												</select>
												<span class="bar"></span>
												<label for="CODE"><?=GRADE_BOOK_TYPE?></label>
											</div>
										</div>
                                    </div>
									
									<div class="row">
                                        <div class="col-md-6">
											<div class="form-group m-b-40">
												<input type="text" class="form-control" id="DESCRIPTION" name="DESCRIPTION" value="<?=$DESCRIPTION?>" >
												<span class="bar"></span>
												<label for="DESCRIPTION"><?=DESCRIPTION?></label>
											</div>
										</div>
                                    </div>
									
									<div class="row">
                                        <div class="col-md-6">
											<div class="form-group m-b-40">
												<input type="text" class="form-control" id="HOUR" name="HOUR" value="<?=$HOUR?>" >
												<span class="bar"></span>
												<label for="HOUR"><?=HOUR?></label>
											</div>
										</div>
                                    </div>
									
									<div class="row">
                                        <div class="col-md-6">
											<div class="form-group m-b-40">
												<input type="text" class="form-control" id="SESSIONS" name="SESSIONS" value="<?=$SESSIONS?>" >
												<span class="bar"></span>
												<label for="SESSIONS"><?=SESSION?></label>
											</div>
										</div>
                                    </div>
									
									<div class="row">
                                        <div class="col-md-6">
											<div class="form-group m-b-40">
												<input type="text" class="form-control" id="POINTS" name="POINTS" value="<?=$POINTS?>" >
												<span class="bar"></span>
												<label for="POINTS"><?=POINTS?></label>
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
												
												<button type="button" class="btn waves-effect waves-light btn-dark" onclick="window.location.href='manage_grade_book_code'" ><?=CANCEL?></button>
												
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