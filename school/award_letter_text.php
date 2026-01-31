<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/award_letter_text.php");

if($_SESSION['PK_USER'] == 0 || $_SESSION['PK_USER'] == '' || ($_SESSION['PK_ROLES'] != 2 && $_SESSION['PK_ROLES'] != 3) ){ 
	header("location:../index");
	exit;
}

if(!empty($_POST)){
	//echo "<pre>";print_r($_POST);exit;
	$AWARD_LETTER = $_POST;
	
	if($_SESSION['PK_ROLES'] == 3)
		$AWARD_LETTER['PK_CAMPUS'] = $_SESSION['PK_CAMPUS'];
	else if($_SESSION['PK_ROLES'] == 2)
		$AWARD_LETTER['PK_CAMPUS'] = $_POST['PK_CAMPUS'];
		
	if($_GET['id'] == ''){
		$AWARD_LETTER['PK_ACCOUNT']  = $_SESSION['PK_ACCOUNT'];
		$AWARD_LETTER['CREATED_BY']  = $_SESSION['PK_USER'];
		$AWARD_LETTER['CREATED_ON']  = date("Y-m-d H:i");
		db_perform('S_AWARD_LETTER', $AWARD_LETTER, 'insert');
	} else {
		if($_SESSION['PK_ROLES'] == 3)
			$cond = " AND PK_CAMPUS = '$_SESSION[PK_CAMPUS]' ";
		else 
			$cond = " AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ";
			
		$AWARD_LETTER['EDITED_BY'] = $_SESSION['PK_USER'];
		$AWARD_LETTER['EDITED_ON'] = date("Y-m-d H:i");
		db_perform('S_AWARD_LETTER', $AWARD_LETTER, 'update'," PK_AWARD_LETTER = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' $cond ");
	}
	header("location:manage_award_letter_text");
}
if($_GET['id'] == ''){
	$PK_CAMPUS 	= '';
	$NAME 		= '';
	$CONTENT 	= '';
	$ACTIVE	 	= '';
	
} else {
	if($_SESSION['PK_ROLES'] == 3)
		$cond = " AND PK_CAMPUS = '$_SESSION[PK_CAMPUS]' ";
	else 
		$cond = " AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ";
			
	$res = $db->Execute("SELECT * FROM S_AWARD_LETTER WHERE PK_AWARD_LETTER = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' $cond "); 
	if($res->RecordCount() == 0){
		header("location:manage_award_letter_text");
		exit;
	}
	
	$PK_CAMPUS 	= $res->fields['PK_CAMPUS'];
	$NAME 		= $res->fields['NAME'];
	$CONTENT 	= $res->fields['CONTENT'];
	$ACTIVE  	= $res->fields['ACTIVE'];
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
	<title><?=AWARD_LETTER_PAGE_TITLE?> | <?=$title?></title>
</head>

<body class="horizontal-nav boxed skin-megna fixed-layout">
   <? require_once("pre_load.php"); ?>
    <div id="main-wrapper">
       <? require_once("menu.php"); ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                 <div class="row page-titles">
                    <div class="col-md-12 align-self-center">
                        <h4 class="text-themecolor"><? if($_GET['id'] == '') echo ADD; else EDIT; ?> <?=AWARD_LETTER_PAGE_TITLE?></h4>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <form class="floating-labels m-t-40" method="post" name="form1" id="form1" >
									
									<? if($_SESSION['PK_ROLES'] == 2) { ?>
									<div class="row">
										<div class="col-md-6">
											<div class="form-group m-b-40">
												<select id="PK_CAMPUS" name="PK_CAMPUS" class="form-control required-entry" >
													<option selected></option>
													 <? $res_type = $db->Execute("select OFFICIAL_CAMPUS_NAME,PK_CAMPUS from S_CAMPUS WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by OFFICIAL_CAMPUS_NAME ASC");
													while (!$res_type->EOF) { ?>
														<option value="<?=$res_type->fields['PK_CAMPUS'] ?>" <? if($PK_CAMPUS == $res_type->fields['PK_CAMPUS']) echo "selected"; ?> ><?=$res_type->fields['OFFICIAL_CAMPUS_NAME']?></option>
													<?	$res_type->MoveNext();
													} ?>
												</select>
												<span class="bar"></span> 
												<label for="PK_CAMPUS"><?=CAMPUS?></label>
											</div>
										</div>
									</div>
									<? } ?>
									
									<div class="row">
                                        <div class="col-md-6">
											<div class="form-group m-b-40">
												<input type="text" class="form-control required-entry" id="NAME" name="NAME" value="<?=$NAME?>" >
												<span class="bar"></span>
												<label for="NAME"><?=NAME?></label>
											</div>
										</div>
                                    </div>
									
									<div class="row">
                                        <div class="col-md-12">
											<div class="form-group m-b-40">
												<textarea id="CONTENT" name="CONTENT" class="form-control required-entry" rows="10"><?=$CONTENT?></textarea>
												<span class="bar"></span>
												<label for="DESCRIPTION"><?=DESCRIPTION?></label>
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
												
												<button type="button" class="btn waves-effect waves-light btn-dark" onclick="window.location.href='manage_award_letter_text'" ><?=CANCEL?></button>
												
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