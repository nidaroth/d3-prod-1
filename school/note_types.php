<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/note_types.php");
require_once("check_access.php");

if(check_access('SETUP_STUDENT') == 0 ){
	header("location:../index");
	exit;
}
if(!empty($_POST)){
	//echo "<pre>";print_r($_POST);exit;
	$NOTE_TYPE = $_POST;
	if($_GET['id'] == ''){
		$NOTE_TYPE['TYPE']  	  = $_GET['t'];
		$NOTE_TYPE['PK_ACCOUNT']  = $_SESSION['PK_ACCOUNT'];
		$NOTE_TYPE['CREATED_BY']  = $_SESSION['PK_USER'];
		$NOTE_TYPE['CREATED_ON']  = date("Y-m-d H:i");
		db_perform('M_NOTE_TYPE', $NOTE_TYPE, 'insert');
	} else {
		$NOTE_TYPE['EDITED_BY'] = $_SESSION['PK_USER'];
		$NOTE_TYPE['EDITED_ON'] = date("Y-m-d H:i");
		db_perform('M_NOTE_TYPE', $NOTE_TYPE, 'update'," PK_NOTE_TYPE = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND TYPE = '$_GET[t]' ");
	}
	header("location:manage_note_types?t=".$_GET['t']);
}
if($_GET['id'] == ''){
	$NOTE_TYPE 		= '';
	$PK_DEPARTMENT 	= '';
	$DESCRIPTION 	= '';
	$ACTIVE	 		= '';
	
} else {
	$res = $db->Execute("SELECT * FROM M_NOTE_TYPE WHERE PK_NOTE_TYPE = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND TYPE = '$_GET[t]' "); 
	if($res->RecordCount() == 0){
		header("location:manage_note_types?t=".$_GET['t']);
		exit;
	}
	
	$NOTE_TYPE 		= $res->fields['NOTE_TYPE'];
	$PK_DEPARTMENT 	= $res->fields['PK_DEPARTMENT'];
	$DESCRIPTION 	= $res->fields['DESCRIPTION'];
	$ACTIVE  		= $res->fields['ACTIVE'];
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
	<title><? if($_GET['t'] == 1) echo NOTE_TYPE_PAGE_TITLE; else if($_GET['t'] == 2) echo EVENT_TYPE_PAGE_TITLE; ?> | <?=$title?></title>
</head>

<body class="horizontal-nav boxed skin-megna fixed-layout">
   <? require_once("pre_load.php"); ?>
    <div id="main-wrapper">
       <? require_once("menu.php"); ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                 <div class="row page-titles">
                    <div class="col-md-12 align-self-center">
                        <h4 class="text-themecolor"><? if($_GET['id'] == '') echo ADD; else EDIT; ?> <? if($_GET['t'] == 1) echo NOTE_TYPE_PAGE_TITLE; else if($_GET['t'] == 2) echo EVENT_TYPE_PAGE_TITLE; ?></h4>
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
												<select id="PK_DEPARTMENT" name="PK_DEPARTMENT" class="form-control required-entry" >
													<option selected></option>
													<option value="-1" <? if($PK_DEPARTMENT == -1) echo "selected"; ?> >All Departments</option>
													<? $res_type = $db->Execute("select PK_DEPARTMENT, DEPARTMENT from M_DEPARTMENT WHERE ACTIVE = '1' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ORDER BY DEPARTMENT ASC ");
													while (!$res_type->EOF) { ?>
														<option value="<?=$res_type->fields['PK_DEPARTMENT'] ?>" <? if($res_type->fields['PK_DEPARTMENT'] == $PK_DEPARTMENT) echo "selected"; ?> ><?=$res_type->fields['DEPARTMENT']?></option>
													<?	$res_type->MoveNext();
													} ?>
												</select>
												<span class="bar"></span>
												<label for="PK_DEPARTMENT"><?=DEPARTMENT?></label>
											</div>
										</div>
                                    </div>
									
									<div class="row">
                                        <div class="col-md-6">
											<div class="form-group m-b-40">
												<input type="text" class="form-control required-entry" id="NOTE_TYPE" name="NOTE_TYPE" value="<?=$NOTE_TYPE?>" >
												<span class="bar"></span>
												<label for="NOTE_TYPE">
													<? if($_GET['t'] == 1) echo NOTE_TYPE; else if($_GET['t'] == 2) echo EVENT_TYPE; ?>
												</label>
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
												
												<button type="button" class="btn waves-effect waves-light btn-dark" onclick="window.location.href='manage_note_types?t=<?=$_GET['t']?>'" ><?=CANCEL?></button>
												
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